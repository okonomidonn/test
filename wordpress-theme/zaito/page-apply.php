<?php
if ( ! is_user_logged_in() ) {
    wp_redirect( home_url( '/login/?redirect=' . urlencode( $_SERVER['REQUEST_URI'] ) ) );
    exit;
}
$current_user = wp_get_current_user();
if ( ! in_array( 'zaito_seeker', $current_user->roles ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

$job_id = isset( $_GET['job_id'] ) ? intval( $_GET['job_id'] ) : 0;
if ( ! $job_id ) {
    wp_redirect( home_url( '/jobs/' ) );
    exit;
}
$job = get_post( $job_id );
if ( ! $job || $job->post_type !== 'job_listing' ) {
    wp_redirect( home_url( '/jobs/' ) );
    exit;
}

$apply_error = '';
$apply_errors = zaito_get_form_errors_from_token();
if ( ! empty( $apply_errors ) ) {
    $apply_error = $apply_errors[0];
}
$application_success = isset( $_GET['applied'] ) && $_GET['applied'] === '1';

get_header();

if ( $application_success ) :
?>
  <main class="apply-main">
    <div class="wrap">
      <div class="apply-success">
        <h1>応募が完了しました</h1>
        <p><?php echo esc_html( get_the_title( $job ) ); ?> にご応募いただきありがとうございます。</p>
        <p>企業からの返信をお待ちください。メッセージはマイページからご確認いただけます。</p>
        <a href="<?php echo esc_url( home_url( '/mypage/' ) ); ?>" class="btn btn-accent">
          マイページに戻る
        </a>
      </div>
    </div>
  </main>
<?php else : ?>
  <main class="apply-main">
    <div class="wrap">
      <div class="apply-container">
        <div class="apply-card">
          <h1>求人に応募する</h1>

          <?php if ( $apply_error ) : ?>
            <div class="auth-error"><p><?php echo esc_html( $apply_error ); ?></p></div>
          <?php endif; ?>

          <div class="job-summary">
            <h2><?php echo esc_html( get_the_title( $job ) ); ?></h2>
            <p class="company"><?php echo esc_html( get_post_meta( $job->ID, '_company_name', true ) ); ?></p>
            <div class="job-details">
              <p>
                <strong>時給:</strong>
                <?php
                $min_salary = get_post_meta( $job->ID, '_job_salary', true );
                echo $min_salary ? esc_html( $min_salary ) . '円' : '応相談';
                ?>
              </p>
              <p>
                <strong>勤務形態:</strong>
                <?php echo esc_html( get_post_meta( $job->ID, '_job_type', true ) ); ?>
              </p>
            </div>
          </div>

          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="apply-form">
            <input type="hidden" name="action" value="zaito_apply" />
            <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />

            <div class="form-group">
              <label for="message">応募メッセージ</label>
              <textarea
                id="message"
                name="message"
                rows="8"
                placeholder="自己紹介や質問などを記入してください"
                required
              ></textarea>
              <small>企業に送られるメッセージです。丁寧にご記入ください。</small>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-accent">応募する</button>
              <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="btn btn-outline">
                キャンセル
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
<?php endif; ?>

<?php get_footer(); ?>
