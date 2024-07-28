<?php
/**
 * Batch send MailPoet confirmation emails to unconfirmed subscribers
 * using mailpoet_api->subscribeToLists()
 * @see https://github.com/mailpoet/mailpoet/blob/7be9ade5f4adda657b9ddabf46aa40f6ec6e6e51/mailpoet/lib/API/MP/v1/Subscribers.php#L217
 * 
 * @date 2024-07-21
 * @author snow_raph (https://github.com/snowraph)
 * 
 * Warning: this is strongly discouraged by MailPoet
 * @see https://kb.mailpoet.com/article/314-how-do-i-resend-the-confirmation-email
 * 
 * Reminder: email won't be sent if subscriber has `count_confirmations` > 2, I didn't override this
 */

// config
$test = true;//after tests, set to false
$test_subscriber_id = 666;//for testing: your unconfirmed subscriber id
$option_lastid_name = 'mailpoet_batch_confirm_last_user_id';//wp_options option_name to keep track of processed ids
$mails_per_batch = 1;//be careful !
$listIds = [3];//array of lists id to subscribe the user to
// end config

chdir(__DIR__);#for some cron environments

if ( ! defined( 'ABSPATH' ) )
	require_once __DIR__ . '/wp-load.php';

if (!class_exists(\MailPoet\API\API::class))
	die('class not found');

// Get MailPoet API instance
$mailpoet_api = \MailPoet\API\API::MP('v1');

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo date('[Y-m-d H:i:s]'), PHP_EOL;
$last = (int)get_option($option_lastid_name);
echo "last_id=$last\n";

if($test)
	$q = $wpdb->prepare("SELECT id, count_confirmations FROM {$wpdb->prefix}mailpoet_subscribers WHERE status = %s and id = %d", 'unconfirmed', $test_subscriber_id);
else
	$q = $wpdb->prepare("SELECT id, count_confirmations FROM {$wpdb->prefix}mailpoet_subscribers WHERE status = %s and id > %d and updated_at < NOW() - INTERVAL 7 DAY order by id LIMIT %d", 'unconfirmed', $last, $mails_per_batch);

$rows = $wpdb->get_results($q);
if(empty($rows))
	die("No more unconfirmed subscriber, reset {$option_lastid_name} and/or delete unconfirmed subscribers\n");

$options = [
	'schedule_welcome_email' => false,
	'send_confirmation_email' => true,
	'skip_subscriber_notification' => true,
];
foreach ($rows as $row) {
	echo "{$row->id} ({$row->count_confirmations})\n";
	try {
		$mailpoet_api->subscribeToLists($row->id, $listIds, $options);
	}
	catch(Exception $e) {
		 echo 'Caught exception: ', $e->getMessage(), "\n";
	}
}
if(!$test)
	update_option($option_lastid_name, $row->id, false);

exit(0);
