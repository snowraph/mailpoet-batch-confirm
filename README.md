# mailpoet-batch-confirm
Batch send MailPoet confirmation emails to unconfirmed subscribers.

> [!WARNING]
> This is strongly discouraged by MailPoet: [https://kb.mailpoet.com/article/314-how-do-i-resend-the-confirmation-email](https://kb.mailpoet.com/article/314-how-do-i-resend-the-confirmation-email)

> [!CAUTION]
> Keep your numbers low to avoid being blocklisted.

> [!NOTE]
> This is experimental code, please review it before use.

Setup:
+ You have to edit the config section
+ Rename the script to something unguessable and drop it in your WP root directory
+ Schedule a cronjob

Background story: I created this because I needed to re-opt-in an old subscribers list. I send 2 confirmations per hour, so for 2K subscribers it takes 40 days. It's not fast but the hosting provider of this Wordpress instance is not kind with spammers, and they are right !
