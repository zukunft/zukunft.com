<?php

/*

    model/system/job_runner.php - execute the pending non-interactive backend jobs
    ---------------------------

    $job_run is the suggested var name

    the runner reads the pending jobs from the existing job structure, selects the jobs that are due,
    executes them in priority order until a per-run time budget is spent and reports the start, the end
    and any error of each job through the existing logging mechanism and to stdout / stderr so that
    journald captures the output when the runner is started by the systemd zukunft-jobs.service

    the runner is a system process: it acts as the system user and does not depend on a web session,
    so the reactive cache updates triggered by a user request must stay out of the runner (see pending.md)


    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'job.php';
include_once paths::MODEL_SYSTEM . 'job_cache_refresh.php';
include_once paths::MODEL_SYSTEM . 'job_db_cleanup.php';
include_once paths::MODEL_SYSTEM . 'job_exe.php';
include_once paths::MODEL_SYSTEM . 'job_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_TYPES . 'job_statuum.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuum;
use DateTime;
use Throwable;

class job_runner
{

    /*
     * const
     */

    // the per-run time budget in seconds; the systemd timer fires minutely,
    // so the default leaves headroom before the next run is started
    const int RUN_BUDGET_SEC = 50;

    // the exit codes returned to the shell and to systemd
    const int EXIT_OK = 0;
    const int EXIT_ERROR = 1;


    /*
     * object vars
     */

    private user $usr; // the system user used to load the pending jobs and to write the status changes


    /*
     * construct
     */

    /**
     * @param user $usr the system user that runs the jobs (see user::system)
     */
    function __construct(user $usr)
    {
        $this->usr = $usr;
    }


    /*
     * run
     */

    /**
     * load the pending jobs and execute the due ones in priority order until the time budget is spent
     * must be called from the command line, because it writes the progress to stdout / stderr
     * @param int $budget_sec the maximum number of seconds this run may use to start new jobs
     * @param DateTime $now the reference time used to decide if a job is due (defaults to the current time)
     * @return int the shell exit code, EXIT_OK if every executed job succeeded, else EXIT_ERROR
     */
    function run(user_message $msg, int $budget_sec = self::RUN_BUDGET_SEC, DateTime $now = new DateTime()): int
    {
        $result = self::EXIT_OK;
        $run_until = microtime(true) + $budget_sec;

        // read the pending jobs from the existing job structure
        $jobs = new job_list($this->usr);
        $jobs->load_by_status(job_statuum::STATUS_NEW, $msg);

        // select the due jobs in execution order
        $due_lst = $this->due_jobs($jobs, $now);
        $this->out(count($due_lst) . ' due job(s) of ' . $jobs->count() . ' pending');

        // execute the due jobs until the time budget is spent
        $count = count($due_lst);
        $pos = 0;
        $done = 0;
        $failed = 0;
        while ($pos < $count and microtime(true) < $run_until) {
            $job = $due_lst[$pos];
            $ok = $this->run_job($job);
            if ($ok) {
                $done++;
            } else {
                $failed++;
            }
            $pos++;
        }

        // report what has not been started so a left-over job is visible in journald
        if ($pos < $count) {
            $this->out('time budget of ' . $budget_sec . ' seconds reached, '
                . ($count - $pos) . ' due job(s) left for the next run');
        }
        if ($failed > 0) {
            $result = self::EXIT_ERROR;
        }
        $this->out('queue finished: ' . $done . ' done, ' . $failed . ' failed');

        // run the periodic maintenance sweeps that are not triggered by a queued job
        $sweep_ok = $this->run_maintenance();
        if (!$sweep_ok) {
            $result = self::EXIT_ERROR;
        }

        return $result;
    }

    /**
     * select the jobs that are due at the given reference time and return them in execution order
     * a job is due if it still has the "new" status and its request time is not in the future;
     * the due jobs are ordered by priority (highest first) and then by request time (oldest first)
     * @param job_list $jobs the pending jobs loaded from the database
     * @param DateTime $now the reference time to compare the request time against
     * @return array the due job objects sorted in the order they should be executed
     */
    function due_jobs(job_list $jobs, DateTime $now): array
    {
        global $sys;
        $result = [];
        $new_id = $sys->typ_lst->job_sta->id(job_statuum::STATUS_NEW);

        // keep only the jobs that are new and whose request time is due
        foreach ($jobs->lst() as $job) {
            $is_due = $this->is_due($job, $new_id, $now);
            if ($is_due) {
                $result[] = $job;
            }
        }

        // order the due jobs by priority and then by request time
        usort($result, [$this, 'compare_jobs']);

        return $result;
    }


    /*
     * run internal
     */

    /**
     * execute a single job, update its status and log the start, the end and any error
     * the status is set to "working" before and to "done" or "failed" after the execution,
     * so a finished (or a crashing) job is not picked up again by the next run
     * @param job $job the due job to execute
     * @return bool true if the job executed without an error
     */
    function run_job(job $job): bool
    {
        $result = true;

        // mark the job as running so a parallel inspection sees the progress
        $this->set_status($job, job_statuum::STATUS_WORKING);
        log_info('job start ' . $job->dsp_id(), 'job_runner->run_job');
        $this->out('start ' . $job->dsp_id());

        // execute the job and catch any error so one failing job does not stop the run
        try {
            $exe_msg = $job->exe();
            if (!$exe_msg->is_ok()) {
                $result = false;
                log_err('job reported errors ' . $job->dsp_id() . ': ' . $exe_msg->all_message_text(), 'job_runner->run_job');
                $this->err('errors ' . $job->dsp_id() . ': ' . $exe_msg->all_message_text());
            }
        } catch (Throwable $e) {
            $result = false;
            log_err('job failed ' . $job->dsp_id() . ': ' . $e->getMessage(), 'job_runner->run_job');
            $this->err('failed ' . $job->dsp_id() . ': ' . $e->getMessage());
        }

        // record the final status so the job is not executed again
        if ($result) {
            $this->set_status($job, job_statuum::STATUS_DONE);
        } else {
            $this->set_status($job, job_statuum::STATUS_FAILED);
        }
        log_info('job end ' . $job->dsp_id(), 'job_runner->run_job');
        $this->out('end ' . $job->dsp_id());

        return $result;
    }

    /**
     * run the periodic maintenance sweeps (cache refresh and database cleanup) as a system process
     * these sweeps are not queued as jobs but run once on every dispatcher cycle within this run
     * @return bool true if every sweep finished without an error
     */
    private function run_maintenance(): bool
    {
        $result = true;
        if (!$this->run_sweep(new job_cache_refresh())) {
            $result = false;
        }
        if (!$this->run_sweep(new job_db_cleanup())) {
            $result = false;
        }
        return $result;
    }

    /**
     * execute one maintenance sweep and log how many items it processed and any error
     * @param job_exe $handler the maintenance sweep to run
     * @return bool true if the sweep finished without an error
     */
    private function run_sweep(job_exe $handler): bool
    {
        $result = true;
        $msg = new user_message($this->usr);
        $type = $handler->type_code_id();

        try {
            $count = $handler->execute($msg);
            $this->out('sweep ' . $type . ' processed ' . $count . ' item(s)');
        } catch (Throwable $e) {
            $result = false;
            log_err('sweep ' . $type . ' failed: ' . $e->getMessage(), 'job_runner->run_sweep');
            $this->err('sweep ' . $type . ' failed: ' . $e->getMessage());
        }
        if (!$msg->is_ok()) {
            $result = false;
            log_err('sweep ' . $type . ' reported: ' . $msg->all_message_text(), 'job_runner->run_sweep');
            $this->err('sweep ' . $type . ' reported: ' . $msg->all_message_text());
        }

        return $result;
    }

    /**
     * check if a job should be executed at the given reference time
     * @param job $job the job to check
     * @param int $new_id the status id that marks a job as pending
     * @param DateTime $now the reference time
     * @return bool true if the job is new and its request time is not in the future
     */
    private function is_due(job $job, int $new_id, DateTime $now): bool
    {
        $result = false;
        if ($job->status_id() === $new_id) {
            if ($job->request_time === null or $job->request_time <= $now) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * compare two due jobs to sort them by descending priority and then by ascending request time
     * @param job $job_a the first job to compare
     * @param job $job_b the second job to compare
     * @return int negative if $job_a runs before $job_b, positive if after, zero if the order is equal
     */
    private function compare_jobs(job $job_a, job $job_b): int
    {
        $result = ($job_b->priority ?? 0) <=> ($job_a->priority ?? 0);
        if ($result === 0) {
            $result = $job_a->request_time <=> $job_b->request_time;
        }
        return $result;
    }

    /**
     * set the job status by its code id and persist the change
     * the status id is written directly (not via job->set_status) so the runner works as a system
     * process without a permission check and independent of the job's requesting user
     * @param job $job the job whose status should be changed
     * @param string $status_code_id the code id of the new status e.g. job_statuum::STATUS_DONE
     * @return void
     */
    private function set_status(job $job, string $status_code_id): void
    {
        global $sys;
        $msg = new user_message($this->usr);
        $job->status_id = $sys->typ_lst->job_sta->id($status_code_id);
        $saved = $job->save($msg);
        if (!$saved) {
            log_err('cannot save status ' . $status_code_id . ' for ' . $job->dsp_id()
                . ': ' . $msg->all_message_text(), 'job_runner->set_status');
            $this->err('cannot save status ' . $status_code_id . ' for ' . $job->dsp_id());
        }
    }


    /*
     * output
     */

    /**
     * write a status line to stdout so journald captures the normal job progress
     * @param string $line the message to write without the trailing newline
     * @return void
     */
    private function out(string $line): void
    {
        fwrite(STDOUT, $line . PHP_EOL);
    }

    /**
     * write an error line to stderr so journald captures it with the error severity
     * @param string $line the message to write without the trailing newline
     * @return void
     */
    private function err(string $line): void
    {
        fwrite(STDERR, $line . PHP_EOL);
    }

}
