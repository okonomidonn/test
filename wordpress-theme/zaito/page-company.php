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
?>

<main class="company-main">
  <div class="wrap">
    <?php zaito_render_verification_banner(); ?>
    <div class="company-header">
      <h1>企業ダッシュボード</h1>
      <p><?php echo esc_html( $company_name ); ?></p>
    </div>

    <div class="company-grid">
      <aside class="company-sidebar">
        <div class="company-info-card">
          <h3>企業情報</h3>
          <div class="info-item">
            <span class="label">企業名</span>
            <span class="value"><?php echo esc_html( $company_name ); ?></span>
          </div>
          <div class="info-item">
            <span class="label">ご担当者</span>
            <span class="value"><?php echo esc_html( $current_user->first_name ); ?></span>
          </div>
          <div class="info-item">
            <span class="label">メールアドレス</span>
            <span class="value"><?php echo esc_html( $current_user->user_email ); ?></span>
          </div>
          <a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>" class="btn btn-outline">
            情報編集
          </a>
        </div>

        <div class="menu-card">
          <h3>メニュー</h3>
          <a href="<?php echo esc_url( home_url( '/company/' ) ); ?>" class="menu-link active">ダッシュボード</a>
          <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>" class="menu-link">求人一覧</a>
          <a href="<?php echo esc_url( home_url( '/company-applicants/' ) ); ?>" class="menu-link">応募者一覧</a>
          <a href="<?php echo esc_url( home_url( '/chat/' ) ); ?>" class="menu-link">メッセージ</a>
          <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="menu-link logout">ログアウト</a>
        </div>
      </aside>

      <div class="company-content">
        <div class="content-section">
          <div class="section-header">
            <h2>掲載中の求人</h2>
            <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>" class="btn btn-accent">
              求人を作成
            </a>
          </div>

          <?php
          $args = array(
              'post_type' => 'job_listing',
              'posts_per_page' => 5,
              'orderby' => 'date',
              'order' => 'DESC',
              'meta_query' => array(
                  array(
                      'key' => '_company_user_id',
                      'value' => $current_user->ID,
                  ),
              ),
          );
          $jobs = get_posts( $args );

          if ( ! empty( $jobs ) ) :
          ?>
            <div class="jobs-list">
              <?php foreach ( $jobs as $job ) :
                  $app_count = count( get_posts( array(
                      'post_type' => 'zaito_application',
                      'meta_query' => array(
                          array(
                              'key' => 'job_id',
                              'value' => $job->ID,
                          ),
                      ),
                  ) ) );
              ?>
                <div class="job-item">
                  <div class="job-info">
                    <h3><?php echo esc_html( get_the_title( $job ) ); ?></h3>
                    <p class="job-date">掲載日：<?php echo esc_html( get_the_date( 'Y/m/d', $job ) ); ?></p>
                  </div>
                  <div class="job-stats">
                    <span class="stat">応募数: <?php echo esc_html( $app_count ); ?></span>
                  </div>
                  <div class="job-actions">
                    <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="btn btn-outline btn-small">
                      詳細
                    </a>
                    <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $job->ID . '&action=edit' ) ); ?>" class="btn btn-outline btn-small">
                      編集
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <p class="empty-message">掲載中の求人がありません</p>
            <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>" class="btn btn-accent">
              求人を作成する
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
