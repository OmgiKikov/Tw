<?php 
# @*************************************************************************@
# @ @author Mansur Altamirov (Mansur_TL)                                    @
# @ @author_url 1: https://www.instagram.com/mansur_tl                      @
# @ @author_url 2: http://codecanyon.net/user/mansur_tl                     @
# @ @author_email: highexpresstore@gmail.com                                @
# @*************************************************************************@
# @ ColibriSM - The Ultimate Modern Social Media Sharing Platform           @
# @ Copyright (c) 21.03.2020 ColibriSM. All rights reserved.                @
# @*************************************************************************@

require_once("core/settings.php");

$sql_db_host   = (isset($sql_db_host) ? $sql_db_host : "");
$sql_db_user   = (isset($sql_db_user) ? $sql_db_user : "");
$sql_db_pass   = (isset($sql_db_pass) ? $sql_db_pass : "");
$sql_db_name   = (isset($sql_db_name) ? $sql_db_name : "");
$site_url      = (isset($site_url)    ? $site_url    : "");
$mysqli        = new mysqli($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name);
$server_errors = array();

if (mysqli_connect_errno()) {
    $server_errors[] = "Error: Failed to connect to MySQL Server: " . mysqli_connect_error();
}

if (empty($server_errors) != true) {
    foreach ($server_errors as $serv_error) {
        echo "<h3>{$serv_error}</h3>";
    }
    die();
}

require_once("core/libs/DB/vendor/autoload.php");

$query        = $mysqli->query("SET NAMES utf8");
$set_charset  = $mysqli->set_charset('utf8mb4');
$set_charset  = $mysqli->query("SET collation_connection = utf8mb4_unicode_ci");
$db           = new MysqliDb($mysqli);
$curr_version = $db->where('name', 'version')->getOne('cl_configs');
$curr_theme   = $db->where('name', 'theme')->getOne('cl_configs');
$curr_version = (empty($curr_version['value'])) ? 0 : $curr_version['value'];
$curr_theme   = (empty($curr_theme['value'])) ? 'default' : $curr_theme['value'];
$new_version  = '1.0.8';
$update       = (version_compare($curr_version, $new_version) == -1);
$errors       = array();
$update_stat  = false;
$new_sql      = array(
	"ALTER TABLE `cl_sessions` ADD `lifespan` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' AFTER `time`;",
	"ALTER TABLE `cl_users` ADD `pnotif_token` VARCHAR(600) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '{\"token\": \"\",\"type\": \"android\"}' AFTER `wallet`;",
	"ALTER TABLE `cl_users` ADD `refresh_token` VARCHAR(220) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' AFTER `pnotif_token`;",
	"INSERT INTO `cl_configs` (`id`, `title`, `name`, `value`, `regex`) VALUES (NULL, 'Max post length', 'max_post_len', '600', '/^[0-9]{1,11}$/');",
	"ALTER TABLE `cl_users` ADD `settings` VARCHAR(3000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '{\"notifs\":{\"like\":1,\"subscribe\":1,\"subscribe_request\":1,\"subscribe_accept\":1,\"reply\":1,\"repost\":1,\"mention\":1,\"visit\":1}}' AFTER `refresh_token`;",
	"ALTER TABLE `cl_users` CHANGE `profile_privacy` `profile_privacy` ENUM('everyone','followers') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'everyone';",
	"ALTER TABLE `cl_publications` ADD `priv_wcs` ENUM('everyone','followers') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'everyone' AFTER `og_data`;",
	"ALTER TABLE `cl_users` ADD `follow_privacy` ENUM('everyone','approved') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'everyone' AFTER `profile_privacy`;",
	"ALTER TABLE `cl_connections` ADD `status` ENUM('active','pending') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'active' AFTER `following_id`;",
	"ALTER TABLE `cl_publications` ADD `priv_wcr` ENUM('everyone','followers','mentioned') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'everyone' AFTER `priv_wcs`;",
	"ALTER TABLE `cl_publications` ADD `poll_data` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `og_data`;",
	"ALTER TABLE `cl_publications` CHANGE `text` `text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;",
	"ALTER TABLE `cl_publications` CHANGE `poll_data` `poll_data` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;",
	"ALTER TABLE `cl_publications` CHANGE `text` `text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;",
	"ALTER TABLE `cl_publications` CHANGE `type` `type` ENUM('text','video','image','gif','poll') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'text';",
	"ALTER TABLE `cl_users` ADD `swift` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `settings`;",
	"ALTER TABLE `cl_users` ADD `last_swift` VARCHAR(135) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `last_post`;",
	"ALTER TABLE `cl_users` ADD `swift_update` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' AFTER `swift`;",
	"ALTER TABLE `cl_users` CHANGE `cover_orig` `cover_orig` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'upload/default/cover.png';",
	"ALTER TABLE `cl_users` ADD `info_file` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `swift_update`;",
	"UPDATE `cl_users` SET `cover_orig` = 'upload/default/cover.png';"
);

if (isset($_POST['update'])) {
	try {

		foreach ($new_sql as $query) {
			mysqli_query($mysqli, $query);
		}

		$db = $db->where('name','version');
        $up = $db->update('cl_configs',array(
            'value' => $new_version
        ));

	 	$update_stat = true;
	} 

	catch (Exception $e) {
		$errors[] = $e->getMessage();
	} 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Update - ColibriSM</title>
	<link rel="stylesheet" href="<?php echo("$site_url/themes/$curr_theme/statics/css/libs/bootstrap-v4.0.0.min.css"); ?>">
	<script src="<?php echo("$site_url/themes/$curr_theme/statics/js/libs/jquery-3.5.1.min.js"); ?>"></script>
    <style>
        html{scroll-behavior:smooth}body{min-width:340px!important;overflow-x:hidden;background:#f5f5f5;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","Roboto","Oxygen","Ubuntu","Cantarell","Fira Sans","Droid Sans","Helvetica Neue",sans-serif}div.container{width:100%;max-width:100%;min-height:100vh;overflow:auto;display:-webkit-inline-flex;display:-moz-inline-flex;display:-ms-inline-flex;display:-o-inline-flex;display:inline-flex;align-items:center;justify-content:center}div.container div.container-inner{margin:100px 0;padding:20px;background:#fff;border-radius:5px;box-shadow:rgba(0,0,0,.05) 0 1px 10px 0;border-top:3px solid #3ba1f3;min-width:650px;max-width:750px}div.container div.container-inner h1{font-size:24px;line-height:36px;color:#192a32;padding:0;margin:0 0 5px}div.container div.container-inner p{font-size:12px;line-height:16px;color:#616161;text-align:center;opacity:.9}div.container div.container-inner h5{font-size:14px;line-height:16px;color:#2d2f43;padding:0;margin:0 0 10px}div.container div.container-inner ol{width:100%;padding:15px 0 15px 40px;margin:0}div.container div.container-inner ol li{width:100%;padding:10px 0;margin:0;font-size:13px;line-height:16px;border-top:1px solid #eee}div.container div.container-inner ol li:first-child{border-top:none}div.container div.container-inner ol li span.badge{display:inline-block;padding:3px 5px;border-radius:3px}div.container div.container-inner ol li span.badge.bug{background:#f6546a;color:#fff}div.container div.container-inner ol li span.badge.feature{background:#35c85f;color:#fff}div.container div.container-inner ol li span.change-log-li{color:#1fb4ef;font-size:inherit;line-height:inherit;display:inline-block;margin-left:5px}div.container div.container-inner h6{color:#1fb4ef;font-size:12px;line-height:18px;font-weight:400;margin-top:15px;border-radius:5px;border:1px solid #1fb4ef;padding:15px}div.container div.container-inner div.alert{color:#fff;font-size:12px;line-height:18px;font-weight:400;margin-top:15px;border-radius:5px;border:1px solid #3ba1f3;padding:15px;background:#3ba1f3}div.container div.container-inner div.ctrls{width:100%;overflow:hidden;margin-top:20px}div.container div.container-inner div.ctrls a{text-decoration:none;line-height:0;outline:0}div.container div.container-inner div.ctrls button{border:1px solid #3ba1f3;background:transparent;color:#3ba1f3;font-size:14px;line-height:14px;padding:15px 40px;margin-right:15px;border-radius:5px}div.container div.container-inner div.ctrls button.in-line{background:#3ba1f3;color:#fff}div.container div.container-inner div.script-up-to-date h4{font-size:13px;color:#3ba1f3;margin:0;padding:15px;border-radius:5px;border:1px solid #3ba1f3;font-weight:400}div.container div.container-inner div.update-completed h4{font-size:13px;color:#35c85f;margin:0;padding:15px;border-radius:5px;border:1px solid #35c85f;font-weight:400}div.container div.container-inner div.update-completed h4 a{font-size:inherit;color:#003569;text-decoration:underline}div.container div.container-inner div.errors-list{width:100%;overflow:hidden;border:1px solid #3ba1f3;padding:15px;border-radius:5px;width:100%;overflow:hidden;border:1px solid #3ba1f3;padding:15px;border-radius:5px}div.container div.container-inner div.errors-list ol{padding:0 0 0 15px}div.container div.container-inner div.errors-list ol li{border-bottom:none;border-top:none;color:#3ba1f3}
    </style>
	<script>
		jQuery(document).ready(function($) {
			$('[data-section-block="script-update"]').find('button[data-section-node="update"]').on('click', function(event) {
				event.preventDefault();
				if ($(this).data('status') == 'updating') {
					alert('Please wait for the update process to complete!');
					return false;
				}
				else {
					$(this).text('Updating, Please wait....');
					$(this).data('status','updating');
					$(this).parents().find('form[data-section-node="submit-form"]').trigger('submit');
				}
			});
		});
	</script>
</head>
<body>
	<div class="container" data-section-block="script-update">
		<div class="container-inner">
			<h1 class="text-center">Update to v<?php echo($new_version); ?></h1>
			<p>
				Welcome to the new version!
				The new version of ColibriSM will make your work more<br>  convenient, safe, high-quality and fast.
			</p>
			<?php if ($update == true && $update_stat == false): ?>
				<h5>
					Change log of [<?php echo($new_version); ?>]:
				</h5>
				<ol>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Controlling the length of post characters from the admin panel (600 / or arbitrary)</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] API endpoints</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] System of notification settings</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Enabled] Login with username</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Dark / light theme mode</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Profile privacy (Who can see profile)</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Improved] Post OG data system</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Post privacy (Who can reply)</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Polls system</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Video lightbox system</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Swifts system (User stories system)</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Admin ads management system</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Ad page view system (For admin)</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] Follow privacy system (Who can follow me)</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Added] GDRP system (Import my information)</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Improved] Pages loading speed</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">[Improved] Improved interface design</span>
					</li>
					<li>
						<span class="badge bug">Bug</span> 
						<span class="change-log-li">[Fixed] Previous bugs</span>
					</li>
					<li>
						<span class="badge feature">Feature</span> 
						<span class="change-log-li">And many more improvements in the core of the script and improved system quality</span>
					</li>
				</ol>
				<div class="alert alert-danger">
					<b>Attention:</b>
					Make sure that you read and follow all the steps listed in the documentation for updating scripts, otherwise the process will result in an error and new updates will not be installed correctly.
                </div>
				<h6>
					If you are not sure that you are installing the latest updates, then click on the link below to «Download» the latest version; otherwise, click «Update» to apply new changes in the version [v<?php echo($new_version); ?>]
				</h6>
				<div class="ctrls">
					<form method="post" data-section-node="submit-form" action="<?php echo($site_url); ?>/update.php">
						<a href="https://codecanyon.net/item/colibrism-the-ultimate-php-modern-social-media-sharing-platform/26612898" target="_blank">
							<button type="button" class="out-line">
								Download
							</button>
						</a>
						<button type="submit" class="in-line" data-section-node="update" data-status="none">
							Update to [v<?php echo($new_version); ?>]
						</button>
						<input type="hidden" name="update" value="1">
					</form>
				</div>
			<?php else: ?>
				<?php if (empty($errors) != true): ?>
					<div class="errors-list">
						<ol>
							<?php foreach ($errors as $err): ?>
								<li>
									<?php echo($err); ?>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				<?php else: ?>
					<?php if ($update_stat == true): ?>
						<div class="update-completed">
							<h4>
								ColibriSM has been updated to version [v<?php echo($new_version); ?>].
								<a href="<?php echo($site_url); ?>">
									Click here to check new updates
								</a>
							</h4>	
						</div>
					<?php else: ?>
						<div class="script-up-to-date">
							<h4>
								ColibriSM - The current version <b>[v<?php echo($new_version); ?>]</b> is up to date!
							</h4>	
						</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>

