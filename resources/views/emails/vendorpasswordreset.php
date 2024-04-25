<h4>Hello!</h4><br>
You are receiving this email because we received a password reset request for your account.
<br/><br/>
<a href="<?= url('vendor/reset_password/' . $token); ?>"><button class="btn btn-primary">Reset Password</button></a><br/><br/>
<p>This password link will expire in 10 minutes</p>
<p>If you did not request a password reset, no further action is required.</p>
<p>Regards</p>
<p>Jeeb</p>
<br>
<p>
    If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: <?= url('admin/reset_password/' . $token); ?></p>
