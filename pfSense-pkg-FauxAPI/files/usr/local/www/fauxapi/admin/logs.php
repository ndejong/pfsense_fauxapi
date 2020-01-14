<?php


include_once('util.inc');
include_once('guiconfig.inc');

$pgtitle = array(gettext('System'), gettext('FauxAPI'), gettext('Logs'));
include_once('head.inc');


$tab_array   = array();
$tab_array[] = array(gettext("Credentials"), false, "/fauxapi/admin/credentials.php");
$tab_array[] = array(gettext("Logs"), true, "/fauxapi/admin/logs.php");
$tab_array[] = array(gettext("About"), false, "/fauxapi/admin/about.php");
display_top_tabs($tab_array, true);

if (file_exists("/etc/inc/syslog.inc")) {
    include_once("/etc/inc/syslog.inc"); // master ver. from commit 3a26e7. 
} else {
    include_once("/etc/inc/filter_log.inc");
}

$lines_taken = 50;
$lines_review_default = 500;
$logs = conv_log_filter(
    "{$g['varlog_path']}/system.log",
    $lines_taken,
    $lines_review_default,  // useless. changed in func conv_log_filter
    array("message" => "fauxapi")
);
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">FauxAPI Logs</h2>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-compact sortable-theme-bootstrap" data-sortable>
                <thead>
                    <tr>
                        <th><?= gettext("Time") ?></th>
                        <th><?= gettext("Process") ?></th>
                        <th><?= gettext("PID") ?></th>
                        <th><?= gettext("Message") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($logs) as $filterent) : ?>
                        <tr class="text-nowrap">
                            <td>
                                <?= htmlspecialchars($filterent['time']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($filterent['process']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($filterent['pid']) ?>
                            </td>
                            <td style="word-wrap:break-word; word-break:break-all; white-space:normal">
                                <?= htmlspecialchars($filterent['message']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include('foot.inc');
?>