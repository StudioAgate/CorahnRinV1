
    <h3><?php tr('Personnages enregistrés'); ?> : <?php echo count($characters); ?></h3>
    <ul class="unstyled char_list bl mid"><?php

$tags_order = array(
    'charname'	=>$orderby === 'name'		? ($sort === 'asc' ? '&#x25b2;' : '&#x25bc;') : '',
    'charjob'	=>$orderby === 'jobname'	? ($sort === 'asc' ? '&#x25b2;' : '&#x25bc;') : '',
    'charpeople'=>$orderby === 'people'	? ($sort === 'asc' ? '&#x25b2;' : '&#x25bc;') : '',
    'charorigin'=>$orderby === 'origin'	? ($sort === 'asc' ? '&#x25b2;' : '&#x25bc;') : '',
);

$sort = isset($_PAGE['request']['sort']) ? ($sort == 'asc' ? 'desc' : 'asc') : 'asc';
$output = '
				<li class="bl"><span class="btn btn-block btn-link listlinks">'
    .mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor'=>$tags_order['charname'].'#', 'attr' =>'class="ib charid"', 'params'=>array('orderby'=>'id', 'sort'=>$sort)))
    .mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor'=>$tags_order['charname'].tr('Nom',true), 'attr' =>'class="ib charname"', 'params'=>array('orderby'=>'name', 'sort'=>$sort)))
    .mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor' =>$tags_order['charjob'].tr('Métier',true), 'attr' =>'class="ib charjob"', 'params' =>array('orderby'=>'jobname', 'sort'=>$sort)))
    .mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor' =>$tags_order['charpeople'].tr('Peuple', true),'attr'=>'class="ib charpeople"', 'params' =>array('orderby'=>'people', 'sort'=>$sort)))
    .mkurl(array('val'=>$_PAGE['id'], 'type'=> 'TAG', 'anchor' =>$tags_order['charorigin'].tr('Origine', true),'attr'=>'class="ib charorigin"', 'params' =>array('orderby'=>'origin', 'sort'=>$sort)))
    .'</span></li>';
foreach ($characters as $c) {
    if (strlen($c['char_name']) > 2) {
        $anchor =
            '<span class="ib charid">'.$c['char_id'].'</span>'
            .'<span class="ib charname">'.$c['char_name'].'</span>'
            .'<span class="ib charjob">'.($c['char_jobname'] ? $c['char_jobname'] : ' ('.tr('Personnalisé', true).') '.$c['char_job']).'</span>'
            .'<span class="ib charpeople">'.$c['char_people'].'</span>'
            .'<span class="ib charorigin">'.$c['region_name'].'</span>';
        $output .= '<li class="bl char">'.mkurl(array('val'=>$_PAGE['id'], 'type'=>'TAG', 'anchor'=>$anchor, 'attr'=>'class="bl mid"', 'params'=>$c['char_id'])).'</li>';
    }
}
echo $output;
?>
    </ul><?php
