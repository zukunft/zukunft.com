# systemd units for the zukunft.com backend job scheduler

These units run `bin/job_runner.php` once a minute to execute the periodic backend
jobs (the proactive cache refresh sweep and the database cleanup).

- `zukunft-jobs.service` — `Type=oneshot`; runs the dispatcher as the web user.
- `zukunft-jobs.timer` — `OnCalendar=minutely`; triggers the service.

`install.sh` copies these to `/etc/systemd/system/` (substituting the pod's
`WWW_ROOT` and `WEB_USER`), runs `daemon-reload`, and enables the timer.

Check the status, run once by hand, or remove the scheduler as described in
[docs/deployment.md → backend job scheduler](../../docs/deployment.md#backend-job-scheduler):

```bash
systemctl status zukunft-jobs.timer   # active? next run?
journalctl -u zukunft-jobs            # job start / end / errors
```
