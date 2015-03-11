<div class="field">
    <div class="two columns alpha">
        <label for="per_page"><?php echo __("Modifiez votre code d'établissement :"); ?></label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __("Le code d'établissement est constitué de la lettre B suivi du numéro INSEE de la commune, d’un code type, et d’un numéro de série (9 caractères)."); ?>.</p>
        <div class="input-block">
        <input type="text" class="textinput" name="institution_code" value="<?php echo get_option('institution_code'); ?>" id="institution_code" />
        </div>
    </div>
</div>