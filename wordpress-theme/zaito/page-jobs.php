<?php get_header();

$search_keyword = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$search_salary  = isset( $_GET['salary'] ) ? sanitize_text_field( $_GET['salary'] ) : '';
$search_salary_type = isset( $_GET['salary_type'] ) ? sanitize_text_field( $_GET['salary_type'] ) : '';
$search_category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
$search_employment_type = isset( $_GET['employment_type'] ) ? sanitize_text_field( $_GET['employment_type'] ) : '';
$search_target_input = isset( $_GET['target'] ) && is_array( $_GET['target'] ) ? array_map( 'sanitize_text_field', $_GET['target'] ) : array();
$search_targets = array_intersect( $search_target_input, zaito_target_tag_options() );
$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page = 12;
$zaito_has_search_filters = $search_keyword || $search_salary || $search_salary_type || $search_category || $search_employment_type || ! empty( $search_targets );

$categories_list = zaito_job_categories();
?>

<main class="jobs-listing-main">
  <div class="wrap">
    <div class="jobs-header">
      <div class="section-eyebrow">JOB SEARCH</div>
      <h1>求人を探す</h1>
      <p>あなたに合った在宅ワークが見つかる</p>
    </div>

    <div class="jobs-grid">
      <aside class="jobs-sidebar">
        <div class="filter-card">
          <h3>検索条件</h3>
          <form method="get" class="filter-form">
            <div class="filter-group">
              <label for="search">キーワード</label>
              <input
                type="text"
                id="search"
                name="s"
                placeholder="仕事内容で検索"
                value="<?php echo esc_attr( $search_keyword ); ?>"
              />
            </div>

            <div class="filter-group">
              <label for="salary_type">給与形態</label>
              <select id="salary_type" name="salary_type">
                <option value="">全て</option>
                <?php foreach ( zaito_salary_type_options() as $zaito_opt ) : ?>
                  <option value="<?php echo esc_attr( $zaito_opt ); ?>" <?php selected( $search_salary_type, $zaito_opt ); ?>><?php echo esc_html( $zaito_opt ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="filter-group">
              <label for="salary">金額（以上）</label>
              <input
                type="number"
                id="salary"
                name="salary"
                min="0"
                step="100"
                placeholder="例: 1300"
                value="<?php echo esc_attr( $search_salary ); ?>"
              />
              <small>時給は100円刻み、日給・固定報酬制はまとまった金額でどうぞ</small>
            </div>

            <div class="filter-group">
              <label for="category">仕事の種類</label>
              <select id="category" name="category">
                <option value="">全て</option>
                <?php foreach ( $categories_list as $cat ) : ?>
                  <option value="<?php echo esc_attr( $cat ); ?>" <?php selected( $search_category, $cat ); ?>><?php echo esc_html( $cat ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="filter-group">
              <label for="employment_type">雇用形態</label>
              <select id="employment_type" name="employment_type">
                <option value="">全て</option>
                <?php foreach ( zaito_employment_type_options() as $zaito_opt ) : ?>
                  <option value="<?php echo esc_attr( $zaito_opt ); ?>" <?php selected( $search_employment_type, $zaito_opt ); ?>><?php echo esc_html( $zaito_opt ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="filter-group">
              <label>対象者</label>
              <div class="checkbox-group">
                <?php foreach ( zaito_target_tag_options() as $zaito_target_opt ) : ?>
                  <label class="checkbox-group-item">
                    <input type="checkbox" name="target[]" value="<?php echo esc_attr( $zaito_target_opt ); ?>" <?php checked( in_array( $zaito_target_opt, $search_targets, true ) ); ?> />
                    <?php echo esc_html( $zaito_target_opt ); ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <button type="submit" class="btn btn-accent btn-block">検索</button>
            <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="btn btn-outline btn-block">リセット</a>
          </form>
        </div>
      </aside>

      <div class="jobs-content">
        <?php
        if ( ! post_type_exists( 'job_listing' ) ) {
            echo '<p class="empty-message">求人がまだありません。</p>';
        } else {
            $args = array(
                'post_type'      => 'job_listing',
                'posts_per_page' => $per_page,
                'paged'          => $paged,
                'orderby'        => 'date',
                'order'          => 'DESC',
            );

            if ( $search_keyword ) {
                $args['s'] = $search_keyword;
            }

            $meta_query = array();
            if ( $search_salary ) {
                $meta_query[] = array(
                    'key'     => '_job_salary',
                    'value'   => intval( $search_salary ),
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                );
            }
            if ( $search_salary_type === '時給' ) {
                // 既存のデモ求人には _job_salary_type が未設定のものが多く、表示上は「時給」扱いになっているため
                // 未設定のものも時給としてヒットさせる（完全一致だけだと該当ゼロ件になってしまう）
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        'key'   => '_job_salary_type',
                        'value' => '時給',
                    ),
                    array(
                        'key'     => '_job_salary_type',
                        'compare' => 'NOT EXISTS',
                    ),
                );
            } elseif ( $search_salary_type ) {
                $meta_query[] = array(
                    'key'   => '_job_salary_type',
                    'value' => $search_salary_type,
                );
            }
            if ( $search_category ) {
                $meta_query[] = array(
                    'key'   => '_job_category',
                    'value' => $search_category,
                );
            }
            if ( $search_employment_type ) {
                $meta_query[] = array(
                    'key'   => '_job_employment_type',
                    'value' => $search_employment_type,
                );
            }
            if ( ! empty( $search_targets ) ) {
                // _job_target は「主婦(夫)歓迎、学生歓迎」のように「、」区切りの文字列で
                // 保存されているため、選択したタグのいずれかを含む求人をLIKEで拾う(OR条件)。
                $target_clauses = array( 'relation' => 'OR' );
                foreach ( $search_targets as $zaito_target_value ) {
                    $target_clauses[] = array(
                        'key'     => '_job_target',
                        'value'   => $zaito_target_value,
                        'compare' => 'LIKE',
                    );
                }
                $meta_query[] = $target_clauses;
            }
            if ( ! empty( $meta_query ) ) {
                $args['meta_query'] = $meta_query;
            }

            $jobs_query = new WP_Query( $args );

            if ( $jobs_query->have_posts() ) :
        ?>
          <p class="jobs-result-count"><?php echo esc_html( $jobs_query->found_posts ); ?>件の求人が見つかりました</p>
          <div class="jobs-list">
            <?php while ( $jobs_query->have_posts() ) : $jobs_query->the_post();
                $job = get_post();
            ?>
              <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="card">
                <div class="card-top">
                  <span class="badge <?php echo esc_attr( zaito_category_badge_class( get_post_meta( $job->ID, '_job_category', true ) ) ); ?>"><?php echo esc_html( get_post_meta( $job->ID, '_job_category', true ) ?: '人気' ); ?></span>
                </div>
                <h3><?php echo esc_html( get_the_title( $job ) ); ?></h3>
                <p><?php echo esc_html( get_post_meta( $job->ID, '_company_name', true ) ); ?></p>
                <div class="price">
                  <span class="unit"><?php echo esc_html( get_post_meta( $job->ID, '_job_salary_type', true ) ?: '時給' ); ?> </span>
                  <?php echo esc_html( get_post_meta( $job->ID, '_job_salary', true ) ); ?>円
                </div>
                <div class="tag-list">
                  <?php foreach ( zaito_job_tags( $job->ID ) as $tag ) : ?>
                    <span class="tag"><?php echo esc_html( $tag ); ?></span>
                  <?php endforeach; ?>
                </div>
                <div class="card-footer">
                  <span><?php echo esc_html( get_post_meta( $job->ID, '_job_days', true ) ); ?></span>
                  <span><?php echo esc_html( get_post_meta( $job->ID, '_job_target', true ) ); ?></span>
                </div>
              </a>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>

          <?php if ( $jobs_query->max_num_pages > 1 ) : ?>
            <nav class="jobs-pagination">
              <?php if ( $paged > 1 ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>" class="btn btn-outline btn-small">← 前へ</a>
              <?php endif; ?>
              <span class="jobs-pagination-status"><?php echo esc_html( $paged ); ?> / <?php echo esc_html( $jobs_query->max_num_pages ); ?> ページ</span>
              <?php if ( $paged < $jobs_query->max_num_pages ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>" class="btn btn-outline btn-small">次へ →</a>
              <?php endif; ?>
            </nav>
          <?php endif; ?>
        <?php
            elseif ( $zaito_has_search_filters ) :
                echo '<p class="empty-message">検索条件に合う求人がみつかりません。条件を変えて再度お試しください。</p>';
            else :
                ?>
                <div class="pickup-empty">
                  <p class="pickup-empty-eyebrow">NOW PREPARING</p>
                  <p class="pickup-empty-title">求人企業を募集中です</p>
                  <p class="pickup-empty-text">ZAITOは立ち上げたばかりのサービスです。今なら掲載無料キャンペーン中。<br class="pickup-empty-br">先にワーカー登録しておくと、新着求人をいち早くチェックできます。</p>
                  <div class="pickup-empty-actions">
                    <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="btn btn-accent">ワーカー登録する(1分で完了)</a>
                    <a href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>" class="btn btn-outline">企業の方はこちら</a>
                  </div>
                </div>
                <?php
            endif;
            wp_reset_postdata();
        }
        ?>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
