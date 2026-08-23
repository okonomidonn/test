<?php
if ( ! is_user_logged_in() ) {
    wp_redirect( home_url( '/company-login/' ) );
    exit;
}
$current_user = wp_get_current_user();
if ( ! in_array( 'zaito_company', $current_user->roles ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

get_header();

$company_name = get_user_meta( $current_user->ID, 'company_name', true );

$own_jobs = get_posts( array(
    'post_type'      => 'job_listing',
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'   => '_company_user_id',
            'value' => $current_user->ID,
        ),
    ),
) );
$own_job_ids = wp_list_pluck( $own_jobs, 'ID' );

$applications = array();
if ( ! empty( $own_job_ids ) ) {
    $all_applications = get_posts( array(
        'post_type'      => 'zaito_application',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
    foreach ( $all_applications as $app ) {
        $app_job_id = get_post_meta( $app->ID, 'job_id', true );
        if ( in_array( intval( $app_job_id ), array_map( 'intval', $own_job_ids ), true ) ) {
            $applications[] = $app;
        }
    }
}
?>

<main class="company-main">
  <div class="wrap">
    <div class="company-header">
      <div class="header-avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $company_name ?: $current_user->display_name, 0, 1 ) ); ?></div>
      <div>
        <div class="header-eyebrow">APPLICANTS</div>
        <h1>応募者一覧</h1>
        <p><?php echo esc_html( $company_name ); ?></p>
      </div>
    </div>

    <div class="company-grid">
      <aside class="company-sidebar">
        <div class="menu-card">
          <h3>メニュー</h3>
          <a href="<?php echo esc_url( home_url( '/company/' ) ); ?>" class="menu-link">ダッシュボード</a>
          <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>" class="menu-link">求人一覧</a>
          <a href="<?php echo esc_url( home_url( '/company-applicants/' ) ); ?>" class="menu-link active">応募者一覧</a>
          <a href="<?php echo esc_url( home_url( '/chat/' ) ); ?>" class="menu-link">メッセージ</a>
          <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="menu-link logout">ログアウト</a>
        </div>
      </aside>

      <div class="company-content">
        <div class="content-section">
          <h2>応募一覧</h2>

          <?php if ( ! empty( $applications ) ) : ?>
            <div class="applications-list">
              <?php foreach ( $applications as $app ) :
                  $job_id = get_post_meta( $app->ID, 'job_id', true );
                  $applicant_id = get_post_meta( $app->ID, 'applicant_id', true );
                  $status = get_post_meta( $app->ID, 'status', true );
                  $message = get_post_meta( $app->ID, 'message', true );
                  $job_title = get_the_title( $job_id );
                  $applicant = get_user_by( 'id', $applicant_id );
                  $applicant_name = $applicant ? $applicant->first_name : '不明';
                  $applicant_email = $applicant ? $applicant->user_email : '';
                  $furigana = get_user_meta( $applicant_id, 'furigana', true );
                  $birthdate = get_user_meta( $applicant_id, 'birthdate', true );
                  $phone = get_user_meta( $applicant_id, 'phone', true );
                  $prefecture = get_user_meta( $applicant_id, 'prefecture', true );
                  $education = get_user_meta( $applicant_id, 'education', true );
                  $work_history = get_user_meta( $applicant_id, 'work_history', true );
              ?>
                <div class="application-item" style="grid-template-columns: 1fr auto auto;">
                  <div class="app-info">
                    <h3><?php echo esc_html( $applicant_name ); ?> さん<?php echo $furigana ? '（' . esc_html( $furigana ) . '）' : ''; ?></h3>
                    <p class="company"><?php echo esc_html( $applicant_email ); ?></p>
                    <p>応募求人：<?php echo esc_html( $job_title ); ?></p>
                    <div class="job-info-grid" style="margin:10px 0;">
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
                    <?php if ( $work_history ) : ?>
                      <p><strong>職務経歴・自己PR：</strong><?php echo esc_html( wp_trim_words( $work_history, 60 ) ); ?></p>
                    <?php endif; ?>
                    <p><strong>応募メッセージ：</strong><?php echo esc_html( wp_trim_words( $message, 40 ) ); ?></p>
                  </div>
                  <div class="app-status">
                    <span class="status-badge status-<?php echo esc_attr( $status ); ?>">
                      <?php
                      $status_label = array(
                          'pending'  => '審査中',
                          'accepted' => '採用',
                          'rejected' => '不採用',
                      );
                      echo isset( $status_label[ $status ] ) ? $status_label[ $status ] : '不明';
                      ?>
                    </span>
                  </div>
                  <div class="app-actions">
                    <?php if ( $status === 'pending' ) : ?>
                      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="zaito_update_application_status" />
                        <input type="hidden" name="application_id" value="<?php echo esc_attr( $app->ID ); ?>" />
                        <input type="hidden" name="status" value="accepted" />
                        <?php wp_nonce_field( 'zaito_update_application_status' ); ?>
                        <button type="submit" class="btn btn-accent btn-small">採用する</button>
                      </form>
                      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="zaito_update_application_status" />
                        <input type="hidden" name="application_id" value="<?php echo esc_attr( $app->ID ); ?>" />
                        <input type="hidden" name="status" value="rejected" />
                        <?php wp_nonce_field( 'zaito_update_application_status' ); ?>
                        <button type="submit" class="btn btn-outline btn-small">不採用にする</button>
                      </form>
                    <?php elseif ( $status === 'accepted' ) : ?>
                      <a href="<?php echo esc_url( home_url( '/chat/?conversation_id=' . $app->ID ) ); ?>" class="btn btn-accent btn-small">
                        メッセージ
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <p class="empty-message">応募はまだありません</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
