<?php
/**
 * 在宅バイトの記事。$zaito_guide_slug が空なら記事一覧（/guide/）、あれば記事（/guide/{slug}/）。
 * 記事の本文は lp/guides/{slug}.php。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

$zaito_guides = zaito_guides();
$zaito_g      = $zaito_guide_slug ? $zaito_guides[ $zaito_guide_slug ] : null;
$zaito_canon  = zaito_guide_url( $zaito_guide_slug );
$zaito_crumbs = array(
    array( '@type' => 'ListItem', 'position' => 1, 'name' => 'zaito', 'item' => home_url( '/' ) ),
    array( '@type' => 'ListItem', 'position' => 2, 'name' => '在宅バイトの記事', 'item' => zaito_guide_url() ),
);

if ( $zaito_g ) {
    $zaito_crumbs[] = array( '@type' => 'ListItem', 'position' => 3, 'name' => $zaito_g['h1'], 'item' => $zaito_canon );
    zaito_remote_head( $zaito_g['title'] . '｜zaito', $zaito_g['description'], $zaito_canon, array(
        '@context' => 'https://schema.org',
        '@graph'   => array(
            array( '@type' => 'BreadcrumbList', 'itemListElement' => $zaito_crumbs ),
            array(
                '@type'            => 'Article',
                'headline'         => $zaito_g['h1'],
                'description'      => $zaito_g['description'],
                'datePublished'    => $zaito_g['published'],
                'dateModified'     => $zaito_g['updated'],
                'mainEntityOfPage' => $zaito_canon,
                'image'            => get_template_directory_uri() . '/lp/ogp.png',
                'author'           => array( '@type' => 'Organization', 'name' => 'zaito運営事務局', 'url' => home_url( '/' ) ),
                'publisher'        => array( '@type' => 'Organization', 'name' => 'zaito', 'url' => home_url( '/' ) ),
            ),
        ),
    ) );
} else {
    zaito_remote_head( '在宅バイトの記事｜探し方・おすすめの仕事・注意点｜zaito', '在宅バイトの探し方、大学生におすすめの在宅の仕事、詐欺の見分け方など、出社なしで働きたい人に役立つ記事をまとめています。', $zaito_canon, array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $zaito_crumbs,
    ) );
}
?>
<body>
<?php zaito_remote_header(); ?>
<main class="wrap doc guide">
<?php if ( $zaito_g ) : ?>
  <a class="back" href="<?php echo esc_url( zaito_guide_url() ); ?>"><span class="ms" aria-hidden="true">arrow_back</span>記事一覧へ</a>
  <h1><?php echo esc_html( $zaito_g['h1'] ); ?></h1>
  <p class="g-meta">更新日：<?php echo esc_html( date_i18n( 'Y年n月j日', strtotime( $zaito_g['updated'] ) ) ); ?>　zaito運営事務局</p>
  <?php include __DIR__ . '/guides/' . $zaito_guide_slug . '.php'; ?>

  <?php
  $zaito_others = array_diff_key( $zaito_guides, array( $zaito_guide_slug => 1 ) );
  if ( $zaito_others ) :
      ?>
    <h2>あわせて読みたい</h2>
    <ul>
      <?php foreach ( $zaito_others as $zaito_os => $zaito_og ) : ?>
        <li><a href="<?php echo esc_url( zaito_guide_url( $zaito_os ) ); ?>"><?php echo esc_html( $zaito_og['h1'] ); ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
<?php else : ?>
  <a class="back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="ms" aria-hidden="true">arrow_back</span>求人一覧へ</a>
  <h1>在宅バイトの記事</h1>
  <p class="lead">在宅バイトの探し方や、おすすめの仕事、注意点をまとめています。</p>
  <ul class="g-index">
    <?php foreach ( $zaito_guides as $zaito_os => $zaito_og ) : ?>
      <li><a href="<?php echo esc_url( zaito_guide_url( $zaito_os ) ); ?>"><b><?php echo esc_html( $zaito_og['h1'] ); ?></b><span><?php echo esc_html( $zaito_og['description'] ); ?></span></a></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
</main>
<?php zaito_remote_footer(); ?>
</body>
</html>
