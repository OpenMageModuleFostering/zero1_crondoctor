<h1>Zero1 CronDoctor<h1><hr />

<h2>Install Instructions</h2>
<ol>
    <li>Stop magento core cron, you can do this via either stop the crond service or simply commenting out the relative line in your cron tab</li>
    <li>
        wait for all cron processes to finish<br />
        <pre>watch -n1 "ps aux | grep cron.php | grep -v grep"</pre><br />
    </li>
    <li>Install the module as normal, via FTP/Git/MCM</li>
    <li>Flush both caches</li>
    <li>
        Alter the following configuration options:<br />
        <ul>
            <li>
                System > Configuration > Advanced | System > Cron<br />
                <ul>
                    <li>"Generate Schedules Every" = 15</li>
                    <li>"Schedule Ahead for" = 20</li>
                    <li>"Missed if Not Run Within" = 0</li>
                    <li>"History Cleanup Every" = [AS DESIRED]</li>
                    <li>"Success History Lifetime" = [AS DESIRED]</li>
                    <li>"Failure History Lifetime" = [AS DESIRED]</li>
                </ul>
            </li>
        </ul>
    </li>
    <li>Alter your cron job to point to "zero1/cron.php", and restart crond if needed</li>
    <li>
        confirm jobs are running<br />
        <pre>watch -n1 "ps aux | grep cron.php | grep -v grep"</pre><br />
        on the minute you should see jobs ending with the following:<br />
        <ul>
            <li>-mdefault</li>
            <li>-malways</li>
            <li>-schedule</li>
            <li>-zombie</li>
        </ul>
    </li>
</ol>
<hr />
<h2>Misc</h2>
<h3>Stopping/Starting Cron after installation</h3>
If you need to stop cron, from the root of your magento installation:<br />
<pre>touch zero1/cron_stop.flag</pre><br />
<pre>watch -n1 "ps aux | grep cron.php | grep -v grep"</pre><br />
wait until all jobs are finished.<br />
To restart cron:<br />
<pre>rm zero1/cron_stop.flag</pre><br />
you should then be able to see all jobs restarting
<hr />
<h3>Disaster Recover</h3>
If cron is stopped with the cron_stop.flag being in place, then you could end up with jobs the are permanently running.
This can be observed by going to System > Cron Doctor and checking filtering by status = 'running' if there are more than 2 jobs running there is an issue.<br />
<b>enterprise_refresh_index</b>: is an 'always' job, if this job hasn't ran since cron has been started again i.e status == running and executed_at is before cron was stopped, the status of this job will need changing (to anything but 'running')<br />
Every other job is the same principle as enterprise_refresh_index.

N.B you should expect 0 - 2 jobs running at anyone time if there are 2 jobs running 1 of them should be enterprise_refresh_index, if this is not the case there is an issue.
<hr />