<?php
$traumaPermanent = $char->getTraumatismesPermanents();
$traumaCurable = $char->getTraumatismesCurables();
$endurcissement = $char->get('endurcissement', 0);
?>

    <div class="row-fluid">
        <div class="span6"><div class="alert alert-info"><?php tr('Attention, la somme des valeurs de Trauma et d\'Endurcissement ne doit pas dépasser 20!'); ?></div></div>
    </div>

    <h2><?php tr('Traumatismes'); ?></h2>

    <div class="mb15 row-fluid">
        <div class="span3">
            <div class="control-group">
                <label for="trauma_perma" class="control-label"><?php tr('Trauma permanent'); ?></label>
                <div class="controls">
                    <input id="trauma_perma" name="trauma_perma" type="text" class="input-mini" value="<?php echo $traumaPermanent; ?>" />
                </div>
            </div>
            <div id="trauma_perma_slider" class="data-slider ml10"  data-slider-input="#trauma_perma" data-slider-min="0" data-slider-max="10"></div>
            <div class="clearfix"></div>
        </div>

        <div class="span3">
            <div class="control-group">
                <label for="trauma_curable" class="control-label"><?php tr('Trauma curable'); ?></label>
                <div class="controls">
                    <input id="trauma_curable" name="trauma_curable" type="text" class="input-mini" value="<?php echo $traumaCurable; ?>" />
                </div>
            </div>
            <div id="trauma_curable_slider" class="data-slider ml10"  data-slider-input="#trauma_curable" data-slider-min="0" data-slider-max="10"></div>
            <div class="clearfix"></div>
        </div>
    </div>

    <hr>

    <h2><?php tr('Endurcissement'); ?></h2>

    <div class="mb15 row-fluid">
        <div class="span6">
            <div class="control-group">
                <label for="endur_perma" class="control-label"><?php tr('Endurcissement permanent'); ?></label>
                <div class="controls">
                    <input id="endur_perma" name="endur_perma" type="text" class="input-mini" value="<?php echo $endurcissement; ?>" />
                </div>
            </div>
            <div id="endur_perma_slider" class="data-slider ml10"  data-slider-input="#endur_perma" data-slider-min="0" data-slider-max="20"></div>
            <div class="clearfix"></div>
        </div>

    </div>
