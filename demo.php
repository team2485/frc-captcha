<?php
require_once 'frc-captcha.php';

if (isset($_POST['captcha'])) {
  $message = frc_captcha_verify() ? 'Correct!' : 'Incorrect.';
}

$captcha = frc_captcha();
?><!DOCTYPE html>
<html>
<head>
  <title>FRC CAPTCHA Demo</title>
</head>
<body>
  <h1>FRC CAPTCHA Demo</h1>

  <?php if (isset($message)) : ?>
  <p><?= $message ?></p>
  <?php endif; ?>

  <form method="post">
    <div class="frc-captcha-widget">
      <div class="frc-captcha-image">
        <img src="<?= $captcha['src'] ?>">
      </div>
      <div class="frc-captcha-input">
        <input name="frc_captcha_team" type="text" placeholder="Team Number" autofocus>
        <a href="">Refresh</a>
      </div>
    </div>
    <input name="captcha" type="hidden">
    <button type="submit">Submit</button>
  </form>
</body>
</html>
