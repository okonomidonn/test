<?php get_header(); ?>

<section class="hero">
  <div class="hero-blob-1"></div>
  <div class="hero-blob-2"></div>

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
          <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="btn btn-outline">無料で登録</a>
        </div>
      </div>

      <div class="hero-card">
        <div class="hero-card-panel">
          <div class="hero-card-dots">
            <span></span><span></span><span></span>
          </div>
          <div class="hero-card-hero">
            <div class="hero-card-hero-text">WORK<br>YOUR<br>WAY.</div>
          </div>
          <div class="hero-card-bar"></div>
          <div class="hero-card-bar short"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="feature">
  <div class="wrap">
    <div class="section-head">
      <div class="section-eyebrow">FEATURES</div>
      <h2>「在宅で働く」を、もっと身近に。</h2>
    </div>

    <div class="card-grid">
      <div class="card">
        <div class="card-top">
          <span class="badge badge-mint">完全在宅</span>
          <span style="font-size:14px;font-weight:900;">01</span>
        </div>
        <h3>場所に縛られない</h3>
        <p>通勤ゼロ。自分の好きな場所が仕事場。授業やサークルと両立できます。</p>
      </div>
      <div class="card">
        <div class="card-top">
          <span class="badge badge-pink">学生歓迎</span>
          <span style="font-size:14px;font-weight:900;">02</span>
        </div>
        <h3>はじめやすい仕事</h3>
        <p>未経験・大学生OKの求人もたくさん。スキルアップできます。</p>
      </div>
      <div class="card">
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
    <div class="section-head-row">
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
        <?php foreach ( $featured_jobs as $job ) : ?>
          <a href="<?php echo esc_url( get_permalink( $job ) ); ?>" class="card">
            <div class="card-top">
              <span class="badge badge-mint">人気</span>
            </div>
            <h3><?php echo esc_html( get_the_title( $job ) ); ?></h3>
            <p><?php echo esc_html( get_post_meta( $job->ID, '_company_name', true ) ); ?></p>
            <div class="price">
              <span class="unit">時給 </span>1,300円〜
            </div>
            <div class="tag-list">
              <span class="tag">#完全在宅</span>
              <span class="tag">#未経験OK</span>
            </div>
            <div class="card-footer">
              <span>週2〜OK</span>
              <span>大学生歓迎</span>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="cta-section">
  <div class="cta-box">
    <div class="cta-blob"></div>
    <div class="cta-content">
      <div class="eyebrow">zaito</div>
      <h2>自分らしく、おうちで働こう。</h2>
      <p>あなたのペースで見つかる、在宅ワーク。</p>
    </div>
    <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="btn btn-cta">求人を探してみる →</a>
  </div>
</section>

<?php get_footer(); ?>
