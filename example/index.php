<?php
/**
 * Google Authenticator
 *
 * @author   Fittipaldi <fittipaldi.gustavo@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/johnstyle/google-authenticator
 */

require_once '../lib/GoogleAuthenticator/GoogleAuthenticator.php';
use GoogleAuthenticator\GoogleAuthenticator;

// /!\ For example ! Don't POST secret key !!
$secretKey = isset($_POST['secretKey']) ? $_POST['secretKey'] : null;

$googleAuthenticator = new GoogleAuthenticator($secretKey);
$appName = 'AppNameExample';

$success = false;
$code = (isset($_POST['code']) && $_POST['code']) ? $_POST['code'] : '';
if ($code && $googleAuthenticator->verifyCode($code)) {
    $success = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Standard Meta -->
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- Site Properties -->
    <title>Google Authenticator - Fittipaldi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/semantic-ui/2.2.10/semantic.min.css">


    <style type="text/css">
        .masthead.segment {
            min-height: 700px;
            padding: 1em 0em;
        }

        .masthead .logo.item img {
            margin-right: 1em;
        }

        .masthead h1.ui.header {
            margin-top: 1em;
            margin-bottom: 0em;
            font-size: 4em;
            font-weight: normal;
        }

        .masthead h2 {
            font-size: 1.7em;
            font-weight: normal;
        }

        .ui.vertical.stripe h3 {
            font-size: 2em;
        }

        .ui.vertical.stripe p {
            font-size: 1.33em;
        }
    </style>

</head>
<body>
<!-- Page Contents -->
<div class="pusher">
    <div class="ui inverted vertical masthead center aligned segment">
        <div class="ui text container">
            <h1 class="ui inverted header">Google Authenticator</h1>
            <h2> Use Google Authenticator for mobile </h2>
            <p>
                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">
                    <img src="https://play.google.com/intl/en_us/badges/images/badge_new.png">
                </a>&nbsp;
                <a href="https://itunes.apple.com/fr/app/google-authenticator/id388497605" target="_blank">
                    <img src="https://it.ku.edu.tr/wp-content/uploads/sites/17/2016/09/download-app-store.png">
                </a>
            </p>
            <p>
                <img src="<?php echo $googleAuthenticator->getQRCodeUrl($appName); ?>" class="ui">
            </p>
            <form method="post">
                <div class="ui input">
                    <input type="hidden" name="secretKey" value="<?php echo $googleAuthenticator->getSecretKey(); ?>"/>
                    <input placeholder="code" type="text" name="code" maxlength="6"/>
                    <button class="ui primary button">Register</button>
                </div>
            </form>
            <?php if ($code): ?>
                <?php if ($success): ?>
                    <div class="ui success message">
                        <div class="header">The code is correct</div>
                    </div>
                <?php else: ?>
                    <div class="ui error message">
                        <div class="header">The code is incorrect</div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>