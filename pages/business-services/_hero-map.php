<?php
/**
 * Shared hero visual: dotted world map with a pin for every listed
 * jurisdiction, arcs from India (Paynancial is headquartered in Patna)
 * to a few labelled locations, and an illustrative journey card.
 * Decorative only (aria-hidden). Used by the landing and global pages.
 */
$bs_map_popular = bs_popular_jurisdictions();
$bs_map_hq = ['lat' => 25.6, 'lon' => 85.1];
$bs_map_xy = fn (array $p) => [round(($p['lon'] + 180) * 4, 1), round((84 - $p['lat']) * 4, 1)];
$bs_map_labelled = ['united-kingdom', 'uae', 'singapore', 'cayman-islands', 'mauritius'];
$bs_map_label_dir = ['uae' => 'is-left', 'cayman-islands' => 'is-left', 'united-kingdom' => 'is-up'];
[$hqX, $hqY] = $bs_map_xy($bs_map_hq);
?>
    <div class="bs-hero-visual reveal" aria-hidden="true">
      <div class="bs-hero-map">
        <img src="<?= e(asset('images/business-services/world-dots-light.svg')) ?>" alt="" width="1440" height="568">
        <svg class="bs-hero-arcs" viewBox="0 0 1440 568" preserveAspectRatio="none">
          <?php foreach ($bs_map_labelled as $slug): [$x, $y] = $bs_map_xy($bs_map_popular[$slug]); $cx = ($hqX + $x) / 2; $cy = min($hqY, $y) - 70; ?>
          <path d="M<?= $hqX ?> <?= $hqY ?> Q<?= $cx ?> <?= $cy ?> <?= $x ?> <?= $y ?>"/>
          <?php endforeach; ?>
        </svg>
        <span class="bs-hero-hq" style="left:<?= round($hqX / 14.4, 2) ?>%;top:<?= round($hqY / 5.68, 2) ?>%;"></span>
        <?php foreach ($bs_map_popular as $slug => $j): $p = bs_map_position($j); ?>
        <span class="bs-hero-pin<?= in_array($slug, $bs_map_labelled, true) ? ' is-labelled' : '' ?><?= isset($bs_map_label_dir[$slug]) ? ' ' . $bs_map_label_dir[$slug] : '' ?>" style="left:<?= $p['x'] * 100 ?>%;top:<?= $p['y'] * 100 ?>%;">
          <?php if (in_array($slug, $bs_map_labelled, true)): ?><em><?= e($j['short'] ?? $j['name']) ?></em><?php endif; ?>
        </span>
        <?php endforeach; ?>
      </div>

      <div class="bs-journey">
        <div class="bs-journey-head">
          <strong>Your incorporation journey</strong>
          <span>Guided end to end</span>
        </div>
        <ol>
          <li class="is-done"><i></i>Requirements shared</li>
          <li class="is-done"><i></i>Structure &amp; jurisdiction chosen</li>
          <li class="is-active"><i></i>Documentation review</li>
          <li><i></i>Incorporation &amp; filing</li>
          <li><i></i>Post-incorporation support</li>
        </ol>
      </div>
    </div>
