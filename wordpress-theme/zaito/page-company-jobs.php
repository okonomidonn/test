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
$update_success = isset( $_GET['updated'] ) && $_GET['updated'] === '1';

// 編集モード: ?edit=IDで、自社の求人のみ編集フォームに読み込む。
$zaito_editing_job = null;
$zaito_edit_id = isset( $_GET['edit'] ) ? intval( $_GET['edit'] ) : 0;
if ( $zaito_edit_id ) {
    $zaito_candidate = get_post( $zaito_edit_id );
    if ( $zaito_candidate && 'job_listing' === $zaito_candidate->post_type
        && (int) get_post_meta( $zaito_edit_id, '_company_user_id', true ) === $current_user->ID ) {
        $zaito_editing_job = $zaito_candidate;
    }
}

get_header();

$company_name = get_user_meta( $current_user->ID, 'company_name', true );
$jobs = get_posts( array(
    'post_type'      => 'job_listing',
    'post_status'    => array( 'publish', 'draft' ),
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
          <h2><?php echo $zaito_editing_job ? '求人を編集する' : '新しい求人を投稿する'; ?></h2>

          <?php if ( $post_success ) : ?>
            <div class="auth-error" style="background:#f1fffb;color:#087f70;"><p>求人を投稿しました</p></div>
          <?php endif; ?>

          <?php if ( $update_success ) : ?>
            <div class="auth-error" style="background:#f1fffb;color:#087f70;"><p>求人を更新しました</p></div>
          <?php endif; ?>

          <?php if ( ! empty( $post_errors ) ) : ?>
            <div class="auth-error">
              <?php foreach ( $post_errors as $error ) : ?>
                <p><?php echo esc_html( $error ); ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php
          // 編集モードの場合は既存の値をフォームに読み込む。
          $zaito_v_title = $zaito_editing_job ? get_the_title( $zaito_editing_job ) : '';
          $zaito_v_content = $zaito_editing_job ? $zaito_editing_job->post_content : '';
          $zaito_v_category = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_category', true ) : '';
          $zaito_v_employment_type = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_employment_type', true ) : '';
          $zaito_v_salary_type = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_salary_type', true ) : '';
          $zaito_v_salary = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_salary', true ) : '';
          $zaito_v_salary_max = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_salary_max', true ) : '';
          $zaito_v_job_type = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_type', true ) : '';
          $zaito_v_job_days = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_days', true ) : '';
          $zaito_v_target = $zaito_editing_job ? array_filter( explode( '、', get_post_meta( $zaito_editing_job->ID, '_job_target', true ) ) ) : array();
          $zaito_v_auto_reply = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_auto_reply_message', true ) : '';
          $zaito_v_screening = $zaito_editing_job ? get_post_meta( $zaito_editing_job->ID, '_job_screening_question', true ) : '';
          ?>

          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="apply-form">
            <?php if ( $zaito_editing_job ) : ?>
              <input type="hidden" name="action" value="zaito_update_job" />
              <input type="hidden" name="job_id" value="<?php echo esc_attr( $zaito_editing_job->ID ); ?>" />
              <?php wp_nonce_field( 'zaito_update_job' ); ?>
            <?php else : ?>
              <input type="hidden" name="action" value="zaito_post_job" />
              <?php wp_nonce_field( 'zaito_post_job' ); ?>
            <?php endif; ?>

            <div class="form-group">
              <label for="title">求人タイトル</label>
              <input type="text" id="title" name="title" value="<?php echo esc_attr( $zaito_v_title ); ?>" required />
            </div>

            <div class="form-group">
              <label for="content">仕事内容</label>
              <textarea id="content" name="content" rows="6" required><?php echo esc_textarea( $zaito_v_content ); ?></textarea>
            </div>

            <div class="job-info-grid" style="margin-bottom:20px;">
              <div class="form-group" style="margin-bottom:0;">
                <label for="category">求人カテゴリ</label>
                <select id="category" name="category" required>
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_job_categories() as $zaito_cat ) : ?>
                    <option value="<?php echo esc_attr( $zaito_cat ); ?>" <?php selected( $zaito_v_category, $zaito_cat ); ?>><?php echo esc_html( $zaito_cat ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label for="employment_type">雇用形態</label>
                <select id="employment_type" name="employment_type" required>
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_employment_type_options() as $zaito_opt ) : ?>
                    <option value="<?php echo esc_attr( $zaito_opt ); ?>" <?php selected( $zaito_v_employment_type, $zaito_opt ); ?>><?php echo esc_html( $zaito_opt ); ?></option>
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
                    <option value="<?php echo esc_attr( $zaito_opt ); ?>" <?php selected( $zaito_v_salary_type, $zaito_opt ); ?>><?php echo esc_html( $zaito_opt ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label for="salary">給与額（下限・数字のみ）</label>
                <input type="text" id="salary" name="salary" value="<?php echo esc_attr( $zaito_v_salary ); ?>" placeholder="例: 1300" />
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label for="salary_max">給与額（上限・任意）</label>
                <input type="text" id="salary_max" name="salary_max" value="<?php echo esc_attr( $zaito_v_salary_max ); ?>" placeholder="例: 1800" />
              </div>
            </div>

            <div class="job-info-grid" style="margin-bottom:20px;">
              <div class="form-group" style="margin-bottom:0;">
                <label for="job_type">勤務体系</label>
                <select id="job_type" name="job_type">
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_work_style_options() as $zaito_opt ) : ?>
                    <option value="<?php echo esc_attr( $zaito_opt ); ?>" <?php selected( $zaito_v_job_type, $zaito_opt ); ?>><?php echo esc_html( $zaito_opt ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label for="job_days">最低勤務日数</label>
                <select id="job_days" name="job_days">
                  <option value="">選択してください</option>
                  <?php foreach ( zaito_min_days_options() as $zaito_opt ) : ?>
                    <option value="<?php echo esc_attr( $zaito_opt ); ?>" <?php selected( $zaito_v_job_days, $zaito_opt ); ?>><?php echo esc_html( $zaito_opt ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>対象者（複数選択可）</label>
              <div class="checkbox-group">
                <?php foreach ( zaito_target_tag_options() as $zaito_opt ) : ?>
                  <label class="checkbox-group-item">
                    <input type="checkbox" name="job_target[]" value="<?php echo esc_attr( $zaito_opt ); ?>" <?php checked( in_array( $zaito_opt, $zaito_v_target, true ) ); ?> />
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
              ><?php echo esc_textarea( $zaito_v_auto_reply ); ?></textarea>
              <small>空欄の場合はダッシュボードの既定メッセージ、それも未設定ならシステムの既定文面が使われます。複数の求人を掲載する場合、求人ごとに文面を変えられます。</small>
            </div>

            <div class="form-group">
              <label for="screening_question">応募者への質問（任意）</label>
              <input
                type="text"
                id="screening_question"
                name="screening_question"
                value="<?php echo esc_attr( $zaito_v_screening ); ?>"
                placeholder="例: 社会人として働いた経験は何年ありますか？"
                maxlength="200"
              />
              <small>設定すると、応募フォームでこの質問への回答が必須になります。応募者一覧で回答を確認できます。</small>
            </div>

            <button type="submit" class="btn btn-accent"><?php echo $zaito_editing_job ? '求人を更新する' : '求人を投稿する'; ?></button>
            <?php if ( $zaito_editing_job ) : ?>
              <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>" class="btn btn-outline">キャンセル</a>
            <?php endif; ?>
          </form>
        </div>

        <div class="content-section" style="margin-top:24px;">
          <h2>投稿済みの求人</h2>

          <?php if ( ! empty( $jobs ) ) : ?>
            <div class="jobs-list">
              <?php foreach ( $jobs as $job ) :
                  $zaito_job_is_open = $job->post_status === 'publish';
              ?>
                <div class="job-item">
                  <div class="job-info">
                    <h3>
                      <?php echo esc_html( get_the_title( $job ) ); ?>
                      <span class="status-badge status-<?php echo $zaito_job_is_open ? 'accepted' : 'rejected'; ?>">
                        <?php echo $zaito_job_is_open ? '掲載中' : '募集終了'; ?>
                      </span>
                    </h3>
                    <p class="job-date">掲載日：<?php echo esc_html( get_the_date( 'Y/m/d', $job ) ); ?></p>
                  </div>
                  <?php
                  // 掲載終了(post_status=draft)の求人はget_permalink()だと404になる
                  // (WordPressは下書き投稿を通常の固定URLでは表示しない仕様のため)。
                  // 自社の求人を確認したい場合はプレビュー用URL(nonce付き)を使う。
                  $zaito_detail_url = $zaito_job_is_open ? get_permalink( $job ) : get_preview_post_link( $job );
                  ?>
                  <div class="job-actions">
                    <a href="<?php echo esc_url( $zaito_detail_url ); ?>" class="btn btn-outline btn-small">詳細</a>
                    <a href="<?php echo esc_url( add_query_arg( 'edit', $job->ID, home_url( '/company-jobs/' ) ) ); ?>" class="btn btn-outline btn-small">編集</a>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
                      <input type="hidden" name="action" value="zaito_toggle_job_status" />
                      <input type="hidden" name="job_id" value="<?php echo esc_attr( $job->ID ); ?>" />
                      <?php wp_nonce_field( 'zaito_toggle_job_status' ); ?>
                      <button type="submit" class="btn btn-outline btn-small">
                        <?php echo $zaito_job_is_open ? '掲載を終了する' : '掲載を再開する'; ?>
                      </button>
                    </form>
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
