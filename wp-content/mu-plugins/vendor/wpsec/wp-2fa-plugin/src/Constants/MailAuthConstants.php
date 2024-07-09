<?php

namespace Wpsec\twofa\Constants;

class MailAuthConstants {
	const AUTH_ACTIVE = 'wpsec_2fa_mail';

	const MAIL_SECRET_DATABASE  = 'wpsec_2fa_mail_secret';
	const MAIL_SUBJECT_DATABASE = 'wpsec_2fa_mail_subject';
	const MAIL_BODY_DATABASE    = 'wpsec_2fa_mail_body';
	const MAIL_FROM_DATABASE    = 'wpsec_2fa_mail_from';

	const MAIL_TEMPLATE_SITE_URL   = '[site-url]';
	const MAIL_TEMPLATE_SITE_NAME  = '[site-name]';
	const MAIL_TEMPLATE_USER_LOGIN = '[login-name]';
	const MAIL_TEMPLATE_CODE       = '[code]';

	const DEFAULT_CODE_EXPIRATION_TIME = 15 * 60;

	const VALIDATION_METHOD = 'email';
}
