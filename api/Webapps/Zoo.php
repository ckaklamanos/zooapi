<?php

function getZooCategory($appalias,$id,$lang,$offset,$items_per_page_global,$order_global) {
	echo json_encode(createZooCategory($appalias,$id,$lang,$offset,$items_per_page_global,$order_global));
}

function getZooItem($id,$lang) {
	echo json_encode(createZooItem($id,$lang));
}

function getZooFrontpage($appalias,$lang,$offset) {

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('*'));
	$query->from('#__zoo_application');
	$query->where('alias="'.$appalias.'"');
	
	$db->setQuery($query);
	$app=$db->loadAssocList();
	$app[0]['params']=json_decode($app[0]['params']);
	
	//Name
	if(property_exists($app[0]['params'],'global.config.name_translation')){
		if(property_exists($app[0]['params']->{'global.config.name_translation'}, $lang)){
			$app[0]['name']=$app[0]['params']->{'global.config.name_translation'}->{$lang};
		}
	}
	//Description
	if(property_exists($app[0]['params'],'content.desc_translation')&&property_exists($app[0]['params']->{'content.desc_translation'}, $lang))
		$app[0]['description']=$app[0]['params']->{'content.desc_translation'}->{$lang};
	
	//Title
	if(property_exists($app[0]['params'],'content.title_translation')&&property_exists($app[0]['params']->{'content.title_translation'}, $lang)){
		$app[0]['title']=$app[0]['params']->{'content.title_translation'}->{$lang};
	}
	elseif(property_exists($app[0]['params'],'content.title')){
		$app[0]['title']=$app[0]['params']->{'content.title'};
	}
	else{
		$app[0]['title']='';
	}
	//Subtitle
	if(property_exists($app[0]['params'],'content.subtitle_translation')&&property_exists($app[0]['params']->{'content.subtitle_translation'}, $lang)){
		$app[0]['subtitle']=$app[0]['params']->{'content.subtitle_translation'}->{$lang};
	}
	elseif(property_exists($app[0]['params'],'content.subtitle')){
		$app[0]['title']=$app[0]['params']->{'content.title'};
	}
	else{
		$app[0]['subtitle']='';
	}
	//Image
	if(property_exists($app[0]['params'],'content.image')){
		$app[0]['image']=$app[0]['params']->{'content.image'};
	}else{
		$app[0]['image']='';
	}
	//Comments
	$app[0]['enable_comments']=$app[0]['params']->{'global.comments.enable_comments'};
	$app[0]['email_notification']=$app[0]['params']->{'global.comments.email_notification'};
	
	//Item order
	$app[0]['item_order']=$app[0]['params']->{'global.config.item_order'}->{'0'};
	$app[0]['item_reversed']=false;
	$app[0]['item_alphanumeric']=false;
	foreach ($app[0]['params']->{'global.config.item_order'} as &$value) {
		if($value=='_reversed')
			$app[0]['item_reversed']=true;
		if($value=='_alphanumeric')
			$app[0]['item_alphanumeric']=true;
	}
	
	//Global items per page
	$app[0]['items_per_page']=$app[0]['params']->{'global.config.items_per_page'};
	$items_per_page_global=$app[0]['items_per_page'];
	
	$application_id=$app[0]['id'];
	//Remove unneeded payload
	unset($app[0]['name']);
	unset($app[0]['id']);
	unset($app[0]['params']);
	unset($app[0]['application_group']);
	
	
	//App frontpage items
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('SQL_CALC_FOUND_ROWS a.id','a.priority','a.application_id','a.state'));
	$query->from('#__zoo_item AS a');
	$query->where('a.state="1"');
	$query->join('INNER', '#__zoo_category_item AS b ON (a.id = b.item_id)');
	$query->where('b.category_id="0"');	
	$query->join('INNER', '#__zoo_application AS c ON (a.application_id = c.id)');
	$query->where('c.id="'.$application_id.'"');	

	$final_orderby=itemsOrderBy($app[0]['item_order'],$app[0]['item_reversed'],$app[0]['item_alphanumeric']);
	$query->order('a.priority DESC'.','.$final_orderby);
	
	$db->setQuery($query,$offset,$items_per_page_global);
	
	$frontpage_items=$db->loadAssocList();
	
	$query_items_no =	"SELECT FOUND_ROWS() as items_no";
	$db->setQuery($query_items_no);
	$items_no=$db->loadAssocList();
	
	$app[0]['items_no']=$items_no[0]['items_no'];
	
	$app[0]['items']=array();
	
	$itemArray=array();
	foreach ($frontpage_items as $key=>$item) {
		$itemArray=createZooItem($item['id'],$lang);
		$app[0]['items'][$key]=$itemArray;
	}
		
	$output=json_encode($app[0]);
	echo $output;
}

function getZooApp($appalias,$lang,$offset) {

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('*'));
	$query->from('#__zoo_application');
	$query->where('alias="'.$appalias.'"');
	
	$db->setQuery($query);
	$app=$db->loadAssocList();
	$app[0]['params']=json_decode($app[0]['params']);
	
	//Name
	if(property_exists($app[0]['params'],'global.config.name_translation')){
		if(property_exists($app[0]['params']->{'global.config.name_translation'}, $lang)){
			$app[0]['name']=$app[0]['params']->{'global.config.name_translation'}->{$lang};
		}
	}
	//Description
	if(property_exists($app[0]['params'],'content.desc_translation')&&property_exists($app[0]['params']->{'content.desc_translation'}, $lang))
		$app[0]['description']=$app[0]['params']->{'content.desc_translation'}->{$lang};
	
	//Title
	if(property_exists($app[0]['params'],'content.title_translation')&&property_exists($app[0]['params']->{'content.title_translation'}, $lang)){
		$app[0]['title']=$app[0]['params']->{'content.title_translation'}->{$lang};
	}
	elseif(property_exists($app[0]['params'],'content.title')){
		$app[0]['title']=$app[0]['params']->{'content.title'};
	}
	else{
		$app[0]['title']='';
	}
	//Subtitle
	if(property_exists($app[0]['params'],'content.subtitle_translation')&&property_exists($app[0]['params']->{'content.subtitle_translation'}, $lang)){
		$app[0]['subtitle']=$app[0]['params']->{'content.subtitle_translation'}->{$lang};
	}
	elseif(property_exists($app[0]['params'],'content.subtitle')){
		$app[0]['title']=$app[0]['params']->{'content.title'};
	}
	else{
		$app[0]['subtitle']='';
	}
	//Image
	if(property_exists($app[0]['params'],'content.image')){
		$app[0]['image']=$app[0]['params']->{'content.image'};
	}else{
		$app[0]['image']='';
	}
	//Comments
	$app[0]['enable_comments']=$app[0]['params']->{'global.comments.enable_comments'};
	$app[0]['email_notification']=$app[0]['params']->{'global.comments.email_notification'};
	
	//Item order
	$app[0]['item_order']=$app[0]['params']->{'global.config.item_order'}->{'0'};
	$app[0]['item_reversed']=false;
	$app[0]['item_alphanumeric']=false;
	foreach ($app[0]['params']->{'global.config.item_order'} as &$value) {
		if($value=='_reversed')
			$app[0]['item_reversed']=true;
		if($value=='_alphanumeric')
			$app[0]['item_alphanumeric']=true;
	}
	
	//Global items per page
	$app[0]['items_per_page']=$app[0]['params']->{'global.config.items_per_page'};
	$items_per_page_global=$app[0]['items_per_page'];
	
	$application_id=$app[0]['id'];
	//Remove unneeded payload
	unset($app[0]['name']);
	unset($app[0]['id']);
	unset($app[0]['params']);
	unset($app[0]['application_group']);
	
	//App categories
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	// Select all articles for users who have a username which starts with 'a'.
	// Order it by the created date.
	$query
		->select(array('a.*'))
		->from('#__zoo_category AS a')
		->join('INNER', '#__zoo_application AS b ON (a.application_id = b.id)')
		->where('b.alias="'.$appalias.'" AND published=1')
		->order('a.ordering ASC');
 
	// Reset the query using our newly populated query object.
	$db->setQuery($query);
 
	// Load the results as a list of stdClass objects.
	$categories = $db->loadObjectList();
	
	//What is the item order that should be queried?
	$order_global=itemsOrderBy($app[0]['item_order'],$app[0]['item_reversed'],$app[0]['item_alphanumeric']);
	
	$app[0]['categories']=array();
	
	foreach ($categories as $key=>$category) {
		$category_result=createZooCategory($appalias,$category->{'id'},$lang,$offset,$items_per_page_global,$order_global);
		if($category_result)
			$app[0]['categories'][$key]=createZooCategory($appalias,$category->{'id'},$lang,$offset,$items_per_page_global,$order_global);
	}
	
	//App frontpage items
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('SQL_CALC_FOUND_ROWS a.id','a.priority','a.application_id','a.state'));
	$query->from('#__zoo_item AS a');
	$query->where('a.state="1"');
	$query->join('INNER', '#__zoo_category_item AS b ON (a.id = b.item_id)');
	$query->where('b.category_id="0"');	
	$query->join('INNER', '#__zoo_application AS c ON (a.application_id = c.id)');
	$query->where('c.id="'.$application_id.'"');	

	$final_orderby=itemsOrderBy($app[0]['item_order'],$app[0]['item_reversed'],$app[0]['item_alphanumeric']);
	$query->order('a.priority DESC'.','.$final_orderby);
	
	$db->setQuery($query,$offset,$items_per_page_global);
	
	$frontpage_items=$db->loadAssocList();
	
	$query_items_no =	"SELECT FOUND_ROWS() as items_no";
	$db->setQuery($query_items_no);
	$items_no=$db->loadAssocList();
	
	$app[0]['items_no']=$items_no[0]['items_no'];
	
	$app[0]['items']=array();
	
	$itemArray=array();
	foreach ($frontpage_items as $key=>$item) {
		$itemArray=createZooItem($item['id'],$lang);
		$app[0]['items'][$key]=$itemArray;
	}
		
	$output=json_encode($app[0]);
	echo $output;
}

function getZooItemComments($id) {
	
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('author','created','content'));
	$query->from('#__zoo_comment');
	$query->where('item_id="'.$id.'" AND state=1');
	$query->order('created DESC');
	
	$db->setQuery($query);
	$comments=$db->loadAssocList();
	
	$output=json_encode($comments);
	echo $output;
}

function createZooCategory($appalias,$id,$lang,$offset,$items_per_page_global,$order_global){

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('*'));
	$query->from('#__zoo_category');
	$query->where('id="'.$id.'"');
	
	$db->setQuery($query);
	$category=$db->loadAssocList();
	$category[0]['params']=json_decode($category[0]['params']);
	
	//Title
	if(property_exists($category[0]['params'],'content.name_translation')&&property_exists($category[0]['params']->{'content.name_translation'}, $lang))
		$category[0]['name']=$category[0]['params']->{'content.name_translation'}->{$lang};
	
	//Subtitle
	if(property_exists($category[0]['params'],'content.sub_headline_translation')&&property_exists($category[0]['params']->{'content.sub_headline_translation'}, $lang)){
		$category[0]['subtitle']=$category[0]['params']->{'content.sub_headline_translation'}->{$lang};
	}
	else{
		$category[0]['subtitle']=$category[0]['params']->{'content.subtitle'};		
	}
	//Description
	if(property_exists($category[0]['params'],'content.desc_translation')&&property_exists($category[0]['params']->{'content.desc_translation'}, $lang))
		$category[0]['description']=$category[0]['params']->{'content.desc_translation'}->{$lang};
	
	//Image
	$category[0]['image']=$category[0]['params']->{'content.image'};

	//Item order
	$category[0]['item_order_global']=true;
	
	if(property_exists($category[0]['params'],'config.item_order')){
		
		$category[0]['item_order_global']=false;
		$category[0]['item_order']=$category[0]['params']->{'config.item_order'}->{'0'};
		$category[0]['item_reversed']=false;
		$category[0]['item_alphanumeric']=false;
	
		foreach ($category[0]['params']->{'config.item_order'} as &$value) {
			if($value=='_reversed')
				$category[0]['item_reversed']=true;
			if($value=='_alphanumeric')
				$category[0]['item_alphanumeric']=true;
		}
	}
	
	//Items per page
	$category[0]['items_per_page_global']=true;
	$items_per_page_final=$items_per_page_global;
	if(property_exists($category[0]['params'],'config.items_per_page')){
		$category[0]['items_per_page_global']=false;
		$category[0]['items_per_page']=$category[0]['params']->{'config.items_per_page'};
		$items_per_page_final=$category[0]['items_per_page'];
	}	

	//Remove unneeded payload
	unset($category[0]['params']);
	unset($category[0]['alias']);
	unset($category[0]['application_id']);
	unset($category[0]['published']);
	
	//Get category items first
	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('SQL_CALC_FOUND_ROWS a.id','a.priority','a.state'));
	$query->from('#__zoo_item AS a');
	$query->where('a.state="1"');
	$query->join('INNER', '#__zoo_category_item AS b ON (a.id = b.item_id)');
	$query->where('b.category_id="'.$id.'"');	
		
	if($category[0]['item_order_global']==true)
		$final_orderby=$order_global;
	else
		$final_orderby=itemsOrderBy($category[0]['item_order'],$category[0]['item_reversed'],$category[0]['item_alphanumeric']);
	
	$query->order('a.priority DESC'.','.$final_orderby);
	
	$db->setQuery($query,$offset,$items_per_page_final);
	
	$category_items=$db->loadAssocList();
	
	$query_items_no =	"SELECT FOUND_ROWS() as items_no";
	$db->setQuery($query_items_no);
	$items_no=$db->loadAssocList();
	
	$category[0]['items_no']=$items_no[0]['items_no'];
	
	$category[0]['items']=array();
	
	$itemArray=array();
	
	foreach ($category_items as $key=>$item) {
		$itemArray=createZooItem($item['id'],$lang);
		$category[0]['items'][$key]=$itemArray;
	}
	
	return $category[0];
	
}

function createZooItem($id,$lang){

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('*'));
	$query->from('#__zoo_item');
	$query->where('id="'.$id.'"');
	
	$db->setQuery($query);
	$item=$db->loadAssocList();
	
	$item[0]['params']=json_decode($item[0]['params']);
	$item[0]['elements']=json_decode($item[0]['elements']);

	//Remove not neede elements and then attach itemtypedataobject to each element
	$item_type_data=itemTypeDataObject('blog',$item[0]['type']);
	
	foreach ($item[0]['elements'] as $key=>$element) {
		
		if(property_exists($item_type_data->elements,$key))
			$element->{'typedata'}=$item_type_data->elements->$key;
		//Remove uneede elements
		if(property_exists($item_type_data->elements,$key)){
			if($item_type_data->elements->$key->type=='separator'||$item_type_data->elements->$key->type=='socialbookmarks'||$item_type_data->elements->$key->type=='socialbuttons'){
				unset($item[0]['elements']->$key);
			}
		}
		//Remove unneeded language elemenents
		if(property_exists($item_type_data->elements,$key)){
			if(property_exists($item_type_data->elements->$key,'zoolingual'))
				if($item_type_data->elements->$key->zoolingual->_languages->{'0'}!=$lang&&!property_exists($item_type_data->elements->$key->zoolingual->_languages,'1')){
					unset($item[0]['elements']->$key);
			}
		}
	}
	
	//Primary category
	$item[0]['primary_category']=$item[0]['params']->{'config.primary_category'};
	//Item comments
	$item[0]['enable_comments']=$item[0]['params']->{'config.enable_comments'};
	
	//Name
	if(property_exists($item[0]['params'],'content.name_translation')&&property_exists($item[0]['params']->{'content.name_translation'}, $lang))
		$item[0]['name']=$item[0]['params']->{'content.name_translation'}->{$lang};
	
	//Remove not needed payload
	//unset($item[0]['id']);
	unset($item[0]['application_id']);
	unset($item[0]['alias']);
	unset($item[0]['hits']);
	unset($item[0]['searchable']);
	unset($item[0]['params']);
	
	return $item[0];
}

function getZooInfo() {

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);
	
	$query->select(array('id', 'alias'));
	$query->from('#__zoo_application');

 	$db->setQuery($query);
	$apps=$db->loadAssocList();
		
	$apps_ts_array=array();
			
	foreach ($apps as &$app) {
		$query = $db->getQuery(true);
		$query->select(array('modified'));
		$query->from('#__zoo_item');
		$query->where('application_id='.$app['id']);
		$query->order('modified DESC');
		$query->limit('1');
	
		$db->setQuery($query);
		$timestamp_row = $db->loadAssocList();		

		if($timestamp_row)
			$apps_ts_array[$app['alias']]=$timestamp_row[0]['modified'];
		else
			$apps_ts_array[$app['alias']]=null;
	}
	
	$info_array=array();
	$info_array['timestamps']=$apps_ts_array;
	$output=json_encode($info_array);
	echo $output;
}

function getZooAppCalendar($appalias,$lang){

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);

	$query
    ->select(array('a.*'))
    ->from('#__zoo_item AS a')
    ->join('INNER', '#__zoo_application AS b ON (a.application_id = b.id)')
    ->where('b.alias="'.$appalias.'" AND a.state=1');

	$db->setQuery($query);
	$calendar_items=$db->loadAssocList();
	
	$events_array=array();
				
	foreach ($calendar_items as $key=>&$item) {
		
		//Initialize the event array
		$event_array=array();
				
		//Event elements		
		$event_elements=json_decode($calendar_items[$key]['elements']);
		
		//Fetch the element data from the xoo json file
		$item_type_data=itemTypeDataObject('blog','eventitem');
		
		//Loop through each of the element
		foreach ($event_elements as $key=>$element) {
			
			//First assign the element type data
			if(property_exists($item_type_data->elements,$key))
				$element->{'typedata'}=$item_type_data->elements->$key;
			
			//Then get the element depending on its type and language
			if($element->{'typedata'}->{'type'}=='date'){
				$ndate=$element->{0}->{'value'};
				//$event_array['date']=$ndate;
				$event_array['date']=date("Y-m-d H:i:s", strtotime("$ndate + 2 hours"));
			}
			if($element->{'typedata'}->{'type'}=='address'&&$element->{'typedata'}->{'zoolingual'}->{'_languages'}->{0}==$lang)
				$event_array['description']=$element->{0}->{'value'};
		}
				
		$item['params']=json_decode($item['params']);

		if(property_exists($item['params']->{'content.name_translation'},$lang)&&$item['params']->{'content.name_translation'}->{$lang}!='')
			$event_array['title']=$item['params']->{'content.name_translation'}->{$lang};
		else
			$event_array['title']=$item['name'];

		$event_array['type']=$item['type'];

		$event_array['url']=dirname(JURI::base()).'/index.php?option=com_zoo&task=item&item_id='.$item['id'];
			
		array_push($events_array, $event_array);
		//array_push($events_array, $event_elements);
	}
	
	$output=json_encode($events_array);
	echo $output;
}

function itemTypeDataObject($app,$type){
	$obj=myfn_read_file_contents(dirname($_SERVER['DOCUMENT_ROOT'].JURI::root( true )).'/media/zoo/applications/'.$app.'/types/'.$type.'.config');
	$obj=json_decode($obj);
	return $obj;
}

function myfn_read_file_contents($file){
	$readHandle = fopen($file, 'r+') or die("can't open file");
	$file_contents = fread($readHandle, filesize($file));
	fclose($readHandle);
	return $file_contents;
}

function itemsOrderBy($item_order,$item_reversed,$item_alphanumeric){


	//Access
	if($item_order=='_itemaccess'&&$item_reversed==false)
		$orderby='a.access ASC';
	if($item_order=='_itemaccess'&&$item_reversed==true)
		$orderby='a.access DESC';
	//Creation Date
	if($item_order=='_itemcreated'&&$item_reversed==false)
		$orderby='a.created ASC, a.modified ASC';
	if($item_order=='_itemcreated'&&$item_reversed==true)
		$orderby='a.created DESC, a.modified DESC';
	//Hits
	if($item_order=='_itemhits'&&$item_reversed==false)
		$orderby='a.hits ASC';
	if($item_order=='_itemhits'&&$item_reversed==true)
		$orderby='a.hits DESC';
	//Modification Date
	if($item_order=='_itemmodified'&&$item_reversed==false)
		$orderby='a.modified DESC';
	if($item_order=='_itemmodified'&&$item_reversed==true)
		$orderby='a.modified ASC';
	//Name
	if($item_order=='_itemname'&&$item_reversed==false)
		$orderby='a.name ASC';
	if($item_order=='_itemname'&&$item_reversed==true)
		$orderby='a.name DESC';	
	//Publish Down date
	if($item_order=='_itempublish_down'&&$item_reversed==false)
		$orderby='a.publish_down ASC';
	if($item_order=='_itempublish_down'&&$item_reversed==true)
		$orderby='a.publish_down DESC';	
	//Publish Up date
	if($item_order=='_itempublish_up'&&$item_reversed==false)
		$orderby='a.publish_up ASC';
	if($item_order=='_itempublish_up'&&$item_reversed==true)
		$orderby='a.publish_up DESC';

	return $orderby;
}

function postZooComment(){

	$request = \Slim\Slim::getInstance()->request();
	
	try {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$columns = array('parent_id', 'item_id', 'user_id', 'user_type','author','email','url','ip','created','content');
		$values = array($_POST["parent_id"],$_POST["item_id"],"'".$_POST["user_id"]."'","'".$_POST["user_type"]."'","'".$_POST["author"]."'","'".$_POST["email"]."'","'".$_POST["url"]."'","'".$request->getIp()."'", "'".date( 'Y-m-d H:i:s')."'","'".$_POST["content"]."'");
		$query
			->insert($db->quoteName('#__zoo_comment'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
			$db->setQuery($query);
		$result = $db->execute();
		
		
		
 		$mailer = JFactory::getMailer();

 		$sender = array( 
			$_POST["email"],
			$_POST["author"]
		);
		
		$mailer->setSender($sender);
		$mailer->addRecipient('info@yesinternet.gr');
 		$body= "Item Id: ".$_POST["item_id"];
		$body.= "\r\n";
		$body.= "Comment: ".$_POST["content"];
		$body.= "\r\n";
		$body.= "IP: ".$request->getIp();
		$body.= "\r\n";
		$body.= "\r\n";
		$body.= "Please log into the admin area in order to review the comment";
		$mailer->setSubject('New comment from the mobile app');
		$mailer->setBody($body);
		$send = $mailer->Send(); 
		
		echo '{"callback":"success","text":"'.$result.'","mail":"'.$send.'"}';

	} catch (Exception $e) {
		echo '{"callback":"error","text":"'. $e->getMessage() .'"}';
	}
 
}




