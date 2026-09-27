<?php
/** Enterprise admin shell — closing markup, command palette and scripts. */
$adm_palette = admin_palette_items($auth_user);
?>
    </main>
  </div>
</div>

<dialog class="adm-cmdk" id="adm-cmdk" aria-label="Command palette">
  <div class="adm-cmdk-box">
    <div class="adm-cmdk-input">
      <?= adm_icon('search', 18) ?>
      <label for="adm-cmdk-q" class="sr-only">Search modules and actions</label>
      <input type="text" id="adm-cmdk-q" placeholder="Search anything… modules, actions, records" autocomplete="off"
             role="combobox" aria-expanded="true" aria-controls="adm-cmdk-list" aria-autocomplete="list">
      <kbd class="adm-kbd">Esc</kbd>
    </div>
    <ul class="adm-cmdk-list" id="adm-cmdk-list" role="listbox" aria-label="Results"></ul>
    <p class="adm-cmdk-foot"><span><kbd class="adm-kbd">↑</kbd><kbd class="adm-kbd">↓</kbd> navigate</span><span><kbd class="adm-kbd">↵</kbd> open</span><span>Only modules and actions you are allowed to use are listed.</span></p>
  </div>
</dialog>
<script type="application/json" id="adm-palette-data"><?= json_encode($adm_palette, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?></script>
<script src="<?= asset('admin/admin.js') ?>" defer></script>
</body>
</html>
