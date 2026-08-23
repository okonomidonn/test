<?php
if ( ! is_user_logged_in() ) {
    wp_redirect( add_query_arg( 'redirect_to', rawurlencode( home_url( $_SERVER['REQUEST_URI'] ) ), home_url( '/login/' ) ) );
    exit;
}
$current_user = wp_get_current_user();
if ( ! zaito_can_use_seeker_features( $current_user ) ) {
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

$furigana = get_user_meta( $current_user->ID, 'furigana', true );
$birthdate = get_user_meta( $current_user->ID, 'birthdate', true );
$phone = get_user_meta( $current_user->ID, 'phone', true );
$prefecture = get_user_meta( $current_user->ID, 'prefecture', true );
$education = get_user_meta( $current_user->ID, 'education', true );
$profile_incomplete = ! $current_user->first_name || ! $furigana || ! $birthdate || ! $phone || ! $prefecture || ! $education;

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

          <?php zaito_render_verification_banner(); ?>

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

          <div class="job-summary" style="margin-top:16px;">
            <h2 style="font-size:16px;">応募者情報（プロフィールより自動入力）</h2>
            <div class="job-info-grid">
              <div class="job-info-item">
                <span class="label">氏名</span>
                <span class="value"><?php echo esc_html( $current_user->first_name ?: '未設定' ); ?></span>
              </div>
              <div class="job-info-item">
                <span class="label">フリガナ</span>
                <span class="value"><?php echo esc_html( $furigana ?: '未設定' ); ?></span>
              </div>
              <div class="job-info-item">
                <span class="label">生年月日</span>
                <span class="value"><?php echo esc_html( $birthdate ?: '未設定' ); ?></span>
              </div>
              <div class="job-info-item">
                <span class="label">電話番号</span>
                <span class="value"><?php echo esc_html( $phone ?: '未設定' ); ?></span>
              </div>
              <div class="job-info-item">
                <span class="label">お住まい</span>
                <span class="value"><?php echo esc_html( $prefecture ?: '未設定' ); ?></span>
              </div>
              <div class="job-info-item">
                <span class="label">最終学歴</span>
                <span class="value"><?php echo esc_html( $education ?: '未設定' ); ?></span>
              </div>
            </div>
            <?php if ( $profile_incomplete ) : ?>
              <p style="margin-top:12px;font-size:13px;color:#d34e79;font-weight:700;">
                プロフィールが未入力の項目があります。応募にはすべての項目の入力が必須です。
              </p>
            <?php else : ?>
              <p style="margin-top:12px;">
                <a href="<?php echo esc_url( home_url( '/worker-profile/' ) ); ?>" class="section-link">プロフィールを編集する →</a>
              </p>
            <?php endif; ?>
          </div>

          <?php if ( $profile_incomplete ) : ?>
            <div class="form-actions" style="margin-top:24px;">
              <a href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( home_url( '/apply/?job_id=' . $job_id ) ), home_url( '/worker-profile/' ) ) ); ?>" class="btn btn-accent">
                プロフィールを入力して応募に進む
              </a>
              <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="btn btn-outline">
                キャンセル
              </a>
            </div>
          <?php else : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="apply-form">
              <input type="hidden" name="action" value="zaito_apply" />
              <input type="hidden" name="job_id" value="<?php echo esc_attr( $job_id ); ?>" />
              <?php wp_nonce_field( 'zaito_apply' ); ?>

              <div class="form-group">
                <label for="message">応募メッセージ・志望動機</label>
                <textarea
                  id="message"
                  name="message"
                  rows="8"
                  placeholder="この求人に応募する理由や、自己紹介・質問などを記入してください"
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
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
<?php endif; ?>

<?php get_footer(); ?>
