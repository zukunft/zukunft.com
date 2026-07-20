<?php

/*

    model/system/job_db_cleanup.php - the database cleanup backend job
    -------------------------------

    $job_clean is the suggested var name

    remove the old completed jobs (successful and failed) from the job table so that the table that
    is written on every value change and page refresh does not grow without a bound; the sweep is
    bounded per run so that it fits into the time budget of the job_runner (see docs/llm/pending.md)


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
include_once paths::MODEL_SYSTEM . 'job_exe.php';
include_once paths::MODEL_SYSTEM . 'job_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_TYPES . 'job_statuum.php';
include_once paths::SHARED_TYPES . 'job_types.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuum;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use DateTime;

class job_db_cleanup implements job_exe
{

    /*
     * const
     */

    // a completed job is kept for this many days before it is removed
    const int RETENTION_DAYS = 30;

    // the maximum number of jobs deleted in one run to keep the run within the time budget
    const int MAX_DELETE = 100;


    /*
     * interface
     */

    /**
     * @return string the code id of the job type this handler executes
     */
    function type_code_id(): string
    {
        return job_types::DB_CLEANUP;
    }

    /**
     * remove the completed jobs whose end time is older than the retention period
     * @param user_message $msg the message buffer that collects a delete problem for the log
     * @return int the number of jobs that have been deleted
     */
    function execute(user_message $msg): int
    {
        $result = 0;
        $usr = user::system();
        $cutoff = new DateTime();
        $cutoff->modify('-' . self::RETENTION_DAYS . ' days');

        // remove the old successful jobs first and then fill the rest of the batch with the failed jobs
        $result += $this->del_old_jobs(job_statuum::STATUS_DONE, $cutoff, $usr, $msg, self::MAX_DELETE);
        $left = self::MAX_DELETE - $result;
        if ($left > 0) {
            $result += $this->del_old_jobs(job_statuum::STATUS_FAILED, $cutoff, $usr, $msg, $left);
        }

        return $result;
    }


    /*
     * internal
     */

    /**
     * delete up to $max jobs of the given status whose end time is older than the cut-off time
     * @param string $status_code_id the code id of the job status to clean up e.g. job_statuum::STATUS_DONE
     * @param DateTime $cutoff jobs finished before this time are removed
     * @param user $usr the system user that loads and deletes the jobs
     * @param user_message $msg the message buffer that collects a delete problem for the log
     * @param int $max the maximum number of jobs to delete in this call
     * @return int the number of jobs that have been deleted
     */
    private function del_old_jobs(
        string       $status_code_id,
        DateTime     $cutoff,
        user         $usr,
        user_message $msg,
        int          $max
    ): int
    {
        $result = 0;
        $jobs = new job_list($usr);
        $jobs->load_by_status($status_code_id);
        foreach ($jobs->lst() as $job) {
            if ($result < $max and $this->is_old($job, $cutoff)) {
                $del_ok = $job->del($msg);
                if ($del_ok) {
                    $result++;
                }
            }
        }
        return $result;
    }

    /**
     * check if a completed job has been finished before the cut-off time
     * @param job $job the completed job to check
     * @param DateTime $cutoff jobs finished before this time are considered old
     * @return bool true if the job has an end time that is older than the cut-off time
     */
    private function is_old(job $job, DateTime $cutoff): bool
    {
        $result = false;
        if ($job->end_time !== null and $job->end_time < $cutoff) {
            $result = true;
        }
        return $result;
    }

}
