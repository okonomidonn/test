<?php
$zaito_is_logged_in = is_user_logged_in();
if ( $zaito_is_logged_in ) {
    $zaito_current_user = wp_get_current_user();
    $zaito_account_url = in_array( 'zaito_company', $zaito_current_user->roles, true )
        ? home_url( '/company/' )
        : home_url( '/mypage/' );
}
get_header();
?>

<section class="hero">
  <div class="wrap">
    <div class="hero-grid">
      <div>
        <div class="hero-badge">
          <span class="hero-badge-dot"></span>
          在宅ワーク専門求人サイト
        </div>

        <h1>
          自分らしく、<br>
          <span class="grad">おうちで働く。</span>
        </h1>

        <p class="lead">
          働く場所も、時間も、もっと自由に。zaitoは「完全在宅」にこだわって、あなたらしい仕事との出会いをつくります。
        </p>

        <div class="tag-list">
          <?php
          $categories = array( 'ライティング', 'デザイン', 'プログラミング', '事務・データ入力', 'カスタマーサポート' );
          foreach ( $categories as $cat ) :
          ?>
            <span class="tag">#<?php echo esc_html( $cat ); ?></span>
          <?php endforeach; ?>
        </div>

        <div class="hero-cta">
          <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="btn btn-accent">求人を探す</a>
          <?php if ( $zaito_is_logged_in ) : ?>
            <a href="<?php echo esc_url( $zaito_account_url ); ?>" class="btn btn-outline">マイページへ</a>
          <?php else : ?>
            <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="btn btn-outline">無料で登録</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="hero-illustration" aria-hidden="true">
        <svg viewBox="0 0 400 420" xmlns="http://www.w3.org/2000/svg" role="img">
          <path class="illust-backdrop" d="M120 8C220 -10 380 30 392 150C404 268 320 330 240 372C158 414 46 404 18 320C-10 236 30 150 62 96C86 54 88 22 120 8Z" />

          <!-- 植物 -->
          <ellipse class="illust-leaf" cx="52" cy="238" rx="20" ry="30" transform="rotate(-18 52 238)" />
          <ellipse class="illust-leaf" cx="70" cy="222" rx="16" ry="26" transform="rotate(14 70 222)" />
          <ellipse class="illust-leaf" cx="60" cy="205" rx="14" ry="22" transform="rotate(-4 60 205)" />
          <rect class="illust-pot" x="40" y="258" width="42" height="34" rx="8" />

          <!-- 机 -->
          <rect class="illust-desk" x="70" y="300" width="290" height="20" rx="6" />
          <rect class="illust-desk-leg" x="90" y="320" width="10" height="60" rx="3" />
          <rect class="illust-desk-leg" x="330" y="320" width="10" height="60" rx="3" />

          <!-- 人物（フラットなシルエット） -->
          <circle class="illust-person" cx="230" cy="222" r="26" />
          <path class="illust-person" d="M182 300C182 262 204 246 230 246C256 246 278 262 278 300V310H182V300Z" />

          <!-- ノートPC -->
          <rect class="illust-laptop-base" x="150" y="296" width="120" height="10" rx="3" />
          <path class="illust-laptop-screen" d="M162 216H258C261 216 263 218 263 221V292H157V221C157 218 159 216 162 216Z" />
          <rect class="illust-laptop-ui" x="170" y="228" width="80" height="8" rx="4" />
          <circle class="illust-laptop-dot" cx="176" cy="252" r="4" />
          <circle class="illust-laptop-dot" cx="192" cy="252" r="4" />
          <circle class="illust-laptop-dot" cx="208" cy="252" r="4" />
          <rect class="illust-laptop-ui" x="170" y="266" width="60" height="6" rx="3" />

          <!-- コーヒーカップ -->
          <path class="illust-cup" d="M296 268H328L324 300H300L296 268Z" />
          <path class="illust-cup-handle" d="M328 274C338 274 338 292 328 292" />

          <!-- 浮遊するアクセント -->
          <circle class="illust-dot dot-1" cx="352" cy="90" r="7" />
          <circle class="illust-dot dot-2" cx="34" cy="120" r="5" />
          <circle class="illust-dot dot-3" cx="368" cy="230" r="5" />
        </svg>
      </div>
    </div>
  </div>
</section>

<section class="section" id="feature">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="section-eyebrow">FEATURES</div>
      <h2>「在宅で働く」を、もっと身近に。</h2>
    </div>

    <div class="feature-grid">
      <div class="card feature-card feature-card-lg reveal">
        <div class="card-top">
          <span class="badge badge-mint">完全在宅</span>
          <span style="font-size:14px;font-weight:900;">01</span>
        </div>
        <h3>場所に縛られない</h3>
        <p>通勤ゼロ。自分の好きな場所が仕事場。授業やサークルと両立できます。</p>
      </div>
      <div class="card feature-card reveal">
        <div class="card-top">
          <span class="badge badge-pink">学生歓迎</span>
          <span style="font-size:14px;font-weight:900;">02</span>
        </div>
        <h3>はじめやすい仕事</h3>
        <p>未経験・大学生OKの求人もたくさん。スキルアップできます。</p>
      </div>
      <div class="card feature-card reveal">
        <div class="card-top">
          <span class="badge badge-yellow">自由な働き方</span>
          <span style="font-size:14px;font-weight:900;">03</span>
        </div>
        <h3>あなたのペースで</h3>
        <p>週2日から、スキマ時間からでも。稼ぎたい分稼げます。</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head-row reveal">
      <div>
        <div class="section-eyebrow">PICK UP</div>
        <h2>今、人気の在宅求人。</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="section-link">すべて見る →</a>
    </div>

    <div class="card-grid">
      <?php
      $featured_jobs = zaito_get_featured_jobs( 3 );
      if ( empty( $featured_jobs ) ) :
      ?>
        <p class="empty">求人がまだありません</p>
      <?php else : ?>
        <?php foreach ( $featured_jobs as $job ) :
          $job_category = get_post_meta( $job->ID, '_job_category', true );
          $job_salary   = get_post_meta( $job->ID, '_job_salary', true );
          $job_days     = get_post_meta( $job->ID, '_job_days', true );
          $job_target   = get_post_meta( $job->ID, '_job_target', true );
        ?>
          <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="card reveal">
            <div class="card-top">
              <span class="badge badge-mint"><?php echo esc_html( $job_category ?: '人気' ); ?></span>
            </div>
            <h3><?php echo esc_html( get_the_title( $job ) ); ?></h3>
            <p><?php echo esc_html( get_post_meta( $job->ID, '_company_name', true ) ); ?></p>
            <div class="price">
              <span class="unit">時給 </span><?php echo $job_salary ? esc_html( $job_salary ) . '円〜' : '応相談'; ?>
            </div>
            <div class="tag-list">
              <span class="tag">#完全在宅</span>
              <span class="tag">#未経験OK</span>
            </div>
            <div class="card-footer">
              <span><?php echo esc_html( $job_days ?: '週2〜OK' ); ?></span>
              <span><?php echo esc_html( $job_target ?: '大学生歓迎' ); ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="cta-section">
  <div class="cta-box reveal">
    <div class="cta-blob"></div>
    <div class="cta-content">
      <div class="eyebrow">zaito</div>
      <h2>自分らしく、おうちで働こう。</h2>
      <?php if ( $zaito_is_logged_in ) : ?>
        <p>あなたに合った在宅ワークがきっと見つかります。</p>
      <?php else : ?>
        <p>登録は無料・1分で完了。今日からあなたに合った在宅ワークを探せます。</p>
      <?php endif; ?>
    </div>
    <div class="cta-actions">
      <?php if ( $zaito_is_logged_in ) : ?>
        <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="btn btn-cta">求人を探す →</a>
        <a href="<?php echo esc_url( $zaito_account_url ); ?>" class="cta-secondary-link">マイページへ</a>
      <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="btn btn-cta">無料で会員登録 →</a>
        <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="cta-secondary-link">まずは求人を見てみる</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php get_footer(); ?>
