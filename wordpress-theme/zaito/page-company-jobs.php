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

$post_errors = zaito_get_form_errors_from_token();
$post_success = isset( $_GET['posted'] ) && $_GET['posted'] === '1';

get_header();

$company_name = get_user_meta( $current_user->ID, 'company_name', true );
$jobs = get_posts( array(
    'post_type'      => 'job_listing',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => array(
        array(
            'key'   => '_company_user_id',
            'value' => $current_user->ID,
        ),
    ),
) );
?>

<main class="company-main">
  <div class="wrap">
    <?php zaito_render_verification_banner(); ?>
    <div class="company-header">
      <div class="header-avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $company_name ?: $current_user->display_name, 0, 1 ) ); ?></div>
      <div>
        <div class="header-eyebrow">JOB LISTINGS</div>
        <h1>求人管理</h1>
        <p><?php echo esc_html( $company_name ); ?></p>
      </div>
    </div>

    <div class="company-grid">
      <aside class="company-sidebar">
        <div class="menu-card">
          <h3>メニュー</h3>
          <a href="<?php echo esc_url( home_url( '/company/' ) ); ?>" class="menu-link">ダッシュボード</a>
          <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>" class="menu-link active">求人一覧</a>
          <a href="<?php echo esc_url( home_url( '/company-applicants/' ) ); ?>" class="menu-link">応募者一覧</a>
          <a href="<?php echo esc_url( home_url( '/chat/' ) ); ?>" class="menu-link">メッセージ</a>
          <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="menu-link logout">ログアウト</a>
        </div>
      </aside>

      <div class="company-content">
        <div class="content-section">
          <h2>新しい求人を投稿する</h2>

          <?php if ( $post_success ) : ?>
            <div class="auth-error" style="background:#f1fffb;color:#087f70;"><p>求人を投稿しました</p></div>
          <?php endif; ?>

          <?php if ( ! empty( $post_errors ) ) : ?>
            <div class="auth-error">
              <?php foreach ( $post_errors as $error ) : ?>
                <p><?php echo esc_html( $error ); ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="apply-form">
            <input type="hidden" name="action" value="zaito_post_job" />
            <?php wp_nonce_field( 'zaito_post_job' ); ?>

            <div class="form-group">
              <label for="title">求人タイトル</label>
              <input type="text" id="title" name="title" required />
            </div>

            <div class="form-group">
              <label for="content">仕事内容</label>
              <textarea id="content" name="content" rows="6" required></textarea>
            </div>

            <div class="job-info-grid" style="margin-bottom:20px;">
              <div class="form-group" style="margin-bottom:0;">
                <label for="category">求人カテゴリ</label>
                <select id="category" name="category" required>
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_job_categories() as $zaito_cat ) : ?>
                    <option value="<?php echo esc_attr( $zaito_cat ); ?>"><?php echo esc_html( $zaito_cat ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label for="employment_type">雇用形態</label>
                <select id="employment_type" name="employment_type" required>
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_employment_type_options() as $zaito_opt ) : ?>
                    <option value="<?php echo esc_attr( $zaito_opt ); ?>"><?php echo esc_html( $zaito_opt ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="job-info-grid" style="margin-bottom:20px;">
              <div class="form-group" style="margin-bottom:0;">
                <label for="salary_type">給与形態</label>
                <select id="salary_type" name="salary_type">
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_salary_type_options() as $zaito_opt ) : ?>
                    <option value="<?php echo esc_attr( $zaito_opt ); ?>"><?php echo esc_html( $zaito_opt ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label for="salary">給与額（下限・数字のみ）</label>
                <input type="text" id="salary" name="salary" placeholder="例: 1300" />
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label for="salary_max">給与額（上限・任意）</label>
                <input type="text" id="salary_max" name="salary_max" placeholder="例: 1800" />
              </div>
            </div>

            <div class="job-info-grid" style="margin-bottom:20px;">
              <div class="form-group" style="margin-bottom:0;">
                <label for="job_type">勤務体系</label>
                <select id="job_type" name="job_type">
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_work_style_options() as $zaito_opt ) : ?>
                    <option value="<?php echo esc_attr( $zaito_opt ); ?>"><?php echo esc_html( $zaito_opt ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label for="job_days">最低勤務日数</label>
                <select id="job_days" name="job_days">
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_min_days_options() as $zaito_opt ) : ?>
                    <option value="<?php echo esc_attr( $zaito_opt ); ?>"><?php echo esc_html( $zaito_opt ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>対象者（複数選択可）</label>
              <div class="checkbox-group">
                <?php foreach ( zaito_target_tag_options() as $zaito_opt ) : ?>
                  <label class="checkbox-group-item">
                    <input type="checkbox" name="job_target[]" value="<?php echo esc_attr( $zaito_opt ); ?>" />
                    <?php echo esc_html( $zaito_opt ); ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="form-group">
              <label for="job_auto_reply_message">この求人専用のファーストメッセージ（任意）</label>
              <textarea
                id="job_auto_reply_message"
                name="job_auto_reply_message"
                rows="4"
                placeholder="<?php echo esc_attr( zaito_default_auto_reply_message() ); ?>"
              ></textarea>
              <small>空欄の場合はダッシュボードの既定メッセージ、それも未設定ならシステムの既定文面が使われます。複数の求人を掲載する場合、求人ごとに文面を変えられます。</small>
            </div>

            <button type="submit" class="btn btn-accent">求人を投稿する</button>
          </form>
        </div>

        <div class="content-section" style="margin-top:24px;">
          <h2>投稿済みの求人</h2>

          <?php if ( ! empty( $jobs ) ) : ?>
            <div class="jobs-list">
              <?php foreach ( $jobs as $job ) : ?>
                <div class="job-item">
                  <div class="job-info">
                    <h3><?php echo esc_html( get_the_title( $job ) ); ?></h3>
                    <p class="job-date">掲載日：<?php echo esc_html( get_the_date( 'Y/m/d', $job ) ); ?></p>
                  </div>
                  <div class="job-actions">
                    <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="btn btn-outline btn-small">詳細</a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <p class="empty-message">まだ求人を投稿していません</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
