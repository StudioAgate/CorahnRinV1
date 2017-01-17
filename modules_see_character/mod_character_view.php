<?php
/** @var int $char_id */
/** @var array $modifications */
/** @var \Symfony\Component\Yaml\Dumper $ymlDumper */

$_PAGE['title_for_layout'] = $charObj->name();

$domains = []; $a = $db->req('SELECT %domain_id, %domain_name, %voie_id FROM %%domains ORDER BY %domain_name ASC');
foreach ($a as $domain) { $domains[$domain['domain_id']] = $domain; }
$disciplines = []; $a = $db->req('SELECT %disc_id, %disc_name FROM %%disciplines');
foreach ($a as $discipline) { $disciplines[$discipline['disc_id']] = $discipline; }

?>
    <h3><?php echo $charObj->name(); ?></h3>
<?php if (P_DEBUG === true) { ?>
    <button class="showhidden btn btn-small"><span class="icon-plus"></span></button><div class="hid"><?php pr(Esterenchar::sdecode_char($char['char_content'])); ?></div>
<?php } ?>
    <div class="row-fluid">
        <div class="span6 sheetlist">
            <?php
            echo mkurl(
                array(
                    'val'=>49,
                    'type'=>'tag',
                    'ext' => 'zip',
                    'anchor'=>'Tout télécharger au format ZIP',
                    'trans' => true,
                    'attr'=>array('class'=>'btn pageview btn-block'),
                    'params'=>array(
                        $char_id,
                        'zip'=>true,
                        clean_word($charObj->name())
                    )
                )
            );
            ?>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span6 sheetlist">
            <br>
            <?php
            echo mkurl(array('val'=>49, 'type'=>'tag', 'ext' => 'pdf', 'anchor'=>'Version originale', 'trans' => true, 'attr'=>array('class'=>'btn pageview'), 'params'=>array($char_id,'pdf'=>true, clean_word($charObj->name()))));
            ?>
            <br>
            <?php
            echo mkurl(array('val'=>49, 'type'=>'tag', 'ext' => 'pdf', 'anchor'=>'Version originale "Printer Friendly"', 'trans' => true, 'attr'=>array('class'=>'btn pageview'), 'params'=>array($char_id,'pdf'=>true,'print'=>true, clean_word($charObj->name()))));
            ?>
        </div>
    </div>

    <hr>

    <h2>Stats:</h2>

    <?php
    $domainsToShow = [];
    foreach ($domains as $domain_id => $domain) {
        $domainValues = $charObj->get('domaines.'.$domain_id);

        $discValues = [];

        $baseBonus =
            + $domainValues['bonus']
            - $domainValues['malus']
            + $charObj->get('voies.'.$domain['voie_id'])['val'];

        if ($domainValues['disciplines']) {
            foreach ($domainValues['disciplines'] as $discId => $discValue) {
                $discValues[tr($disciplines[$discId]['disc_name'], true, [], 'create_char')] = $discValue['val'] + $baseBonus;
            }
        }

        $domainsToShow[tr($domain['domain_name'], true, [], 'create_char')] = [
            'val' => $domainValues['val'] + $baseBonus,
            'disciplines' => $discValues,
        ];
    }

    ?>

    <div class="row-fluid">
        <div class="span3">
            <table class="table table-bordered table-hover table-condensed">
                <tr>
                    <th><?php tr('Voies'); ?></th>
                    <th class="center"><?php tr('Score'); ?></th>
                </tr>
                <?php
                $shownDomains = 0;
                foreach ($charObj->getVoies() as $id => $values) {
                    $domainTrClass = $shownDomains % 2 === 0 ? 'even' : 'odd';
                    ?>
                    <tr class="<?php echo $domainTrClass; ?>">
                        <td class="pl15"><?php tr($values['name'], false, [], 'create_char'); ?></td>
                        <td class="center"><?php echo $values['val']; ?></td>
                    </tr>
                    <?php
                    $shownDomains++;
                }
                ?>
            </table>
        </div>
        <div class="span3">
            <table class="table table-bordered table-hover table-condensed">
                <tr>
                    <th><?php tr('Domaine'); ?></th>
                    <th class="center"><?php tr('Score'); ?></th>
                </tr>
                <?php
                $shownDomains = 0;
                foreach ($domainsToShow as $translatedName => $values) {
                    $domainTrClass = $shownDomains % 2 === 0 ? 'even' : 'odd';
                    ?>
                    <tr class="<?php echo $domainTrClass; ?>">
                        <td class="pl15"><?php echo $translatedName; ?></td>
                        <td class="center"><?php echo $values['val']; ?></td>
                    </tr>
                    <?php
                    if ($values['disciplines']) {
                        foreach ($values['disciplines'] as $discId => $discValue) {
                            ?>
                            <tr class="<?php echo $domainTrClass; ?>">
                                <td class="pl15">&nbsp;&raquo;&nbsp;<?php echo $discId; ?></td>
                                <td class="center"><?php echo $discValue; ?></td>
                            </tr>
                            <?php
                        }
                    }
                    $shownDomains++;
                }
                ?>
            </table>
        </div>
    </div>

    <hr>

<?php if ($modifications && count($modifications)) { ?>
    <h2>Modifications:</h2>
    <div class="row-fluid">
        <div class="span4"><h3 class="center"><?php tr('Date &ndash; utilisateur'); ?></h3></div>
        <div class="span8"><h3 class="center"><?php tr('Modification'); ?></h3></div>
    </div>
    <?php

    foreach ($modifications as $mod) { ?>
        <?php
        $contentBefore = json_decode($mod['charmod_content_before'], true);
        $contentAfter = json_decode($mod['charmod_content_after'], true);
        if (!count($contentBefore) && !count($contentAfter)) { continue; }
        $before = $after = array();
        $processed = load_module('character_diff', 'module', array(
            'before' => $contentBefore,
            'after' => $contentAfter,
            'referenceDomains' => $domains,
            'referenceDisciplines' => $disciplines,
        ));
        if (empty($processed)) { continue; }
        $processed = $ymlDumper->dump($processed, 6, 0);
        $content = $processed;
        $content = str_replace("': '", ': ', $content);
        $content = str_replace("'\n", "\n", $content);
        $content = str_replace("''", "'", $content);
        $content = str_replace(": '", ': ', $content);
        $content = preg_replace('~\n *( *- *)?\'~isUu', "\n  ", $content);
        ?>
        <div class="row-fluid">
            <div class="span4">
                <strong><?php echo date('Y-m-d \&\n\b\s\p\; H:i:s', $mod['charmod_date']); ?></strong> &ndash;
                <?php echo $users[$mod['user_id']]; ?>
            </div>
            <div class="span8">
                <pre><?php echo $content; ?></pre>
            </div>
        </div>
    <?php }
}
