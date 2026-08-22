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

$application_success = false;
$apply_error = '';
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) && $_POST['action'] === 'apply' ) {
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

    if ( ! $message ) {
        $apply_error = 'メッセージを入力してください';
    } else {
        $application_id = wp_insert_post( array(
            'post_type' => 'zaito_application',
            'post_title' => 'Application from ' . $current_user->user_email,
            'post_status' => 'publish',
        ) );

        if ( $application_id ) {
            update_post_meta( $application_id, 'applicant_id', $current_user->ID );
            update_post_meta( $application_id, 'job_id', $job_id );
            update_post_meta( $application_id, 'message', $message );
            update_post_meta( $application_id, 'status', 'pending' );
            $application_success = true;
        }
    }
}

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

          <form method="post" class="apply-form">
            <input type="hidden" name="action" value="apply" />
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
