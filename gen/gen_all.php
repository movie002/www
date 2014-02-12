
<?php
/////////////////////////////////////////////////////
/// 函数名称：gen_all
/// 函数作用：产生静态的页面的html页面
/// 函数作者: DH
/// 作者地址: http://dhblog.org
/////////////////////////////////////////////////////

header('Content-Type:text/html;charset= UTF-8'); 
require("config.php");
require("scan.php");
#需要使用的基础函数
require("compressJS.class.php");
require("page_navi.php");
require("gen_share.php");
require("sitemap/gen.php");
require("article_index.php");
require("genrss/gen.php");
set_time_limit(600); 

//预定义
global $DH_src_path;
//$cats=array('美丽'=>array('xx'),'yy','zz','dd','bb','aa','cc');
//$tags=array();

//取出已经搞定的日期和数目
$countpath=$DH_src_path.'tmp/count';
$content = dh_file_get_contents("$countpath");
preg_match('/<date>(.*?)<\/date><count>(.*?)<\/count>/s',$content,$match);
//print_r($match);
$begindate=$match[1];
$todaydate=date("YmdH");
$begincount=$match[2];

//扫描所有的文件
$lists=array();
$lists_num=array();
$pages=array();
scan_dir($DH_src_path.'pages');
krsort($lists);
gen_lists_num();
ksort($pages);
//print_r($lists);
//print_r($lists_num);

//print_r($pages);
//output_all();

//生成关键信息之后调用gen_share();
dh_gen_share($lists);

//输出到各个lists
dh_gen_list();
//输出到pages
dh_gen_page();

//拷贝index
$DH_input_html  = $DH_output_index_path."all/1.html";
$DH_output_content = dh_file_get_contents("$DH_input_html");
$DH_output_content = preg_replace('/<title>.*?<\/title>/s','<title>DH博客(DH出品,必属精品) 首页</title>',$DH_output_content);
$DH_output_content = preg_replace('/<meta name="keywords" content="[^<]+" \/>/s','<meta name="keywords" content="DH博客,建站技术,编程技术,影视分享,网络推广" />',$DH_output_content);
$DH_output_content = preg_replace('/<meta name="description" content="[^<]+" \/>/s','<meta name="description" content="DH博客是个人的分享博客,针对DH个人的兴趣方向,分享自己的心得,博客内容几乎全部原创.主要方向有:建站技术,编程技术,网站推广,影视分享等." />',$DH_output_content);
dh_file_put_contents($DH_output_path."index.html",$DH_output_content);	
//copy($DH_output_index_path."all/1.html",$DH_output_path."index.html");

//一页网页地图使用多少个项
//资料：Google允许的sitemap数量是1000，现在提升到50000，但是这是理论数据。根据测试，实际情况所对应的以下数据为佳。
//调整前：理论最大1000条URL，实际500以下为佳，
//调整后：理论最大50000条URL，实际2500以下为佳。
$pagecount=2500;
gen_html_date($lists);
gen_html_num($lists,$pagecount);
gen_xml(date("Y-m-d H:i:s"),'weekly',$lists,$pagecount);
gen_siteindex(date("Y-m-d H:i:s"));
gen_sitemapall();

//生成rss
gen_rss($lists,10);

//将搞定的date和count写入文件保存
$endcount=end($lists);
$maxdate=key($lists);
$maxcount=$begincount+count($lists);
echo $maxdate.":".$maxcount;


function dh_gen_page()
{
	global $pages,$DH_home_url,$DH_index_url,$DH_html_path,$DH_output_path,$DH_output_html_path,$DH_src_path,$begincount;
	if (!file_exists($DH_output_html_path))  
		mkdir($DH_output_html_path,0777);
	
	$DH_input_html  = $DH_html_path . 'page.html';
	$DH_output_content = dh_file_get_contents("$DH_input_html");
	$DH_output_content = setshare($DH_output_content,'page.js');
	$DH_output_content = str_replace("%home%",$DH_home_url,$DH_output_content);
	//echo $DH_output_content;
	$i=0;
	foreach($pages as $key=>$page)
	{
		$i++;
		//if($i>4)
		//	break;
		//print_r($page);		
		preg_match('/<\_T>(.*?)<\/\_T>/s',$page,$matchT);
		preg_match('/<\_b>\<!\-\-(.*?)\-\-\><\/\_b>/s',$page,$matchb);
		preg_match('/<\_d>(.*?)<\/\_d>/s',$page,$matchd);
		preg_match('/<\_a>(.*?)<\/\_a>/s',$page,$matcha);
		preg_match('/<\_c>(.*?)<\/\_c>/s',$page,$matchc);
		preg_match_all('/<\_t>(.*?)<\/\_t>/s',$page,$matchts);
		//print_r($match);
		$tags='';
		if(!empty($matchts[1]))
		{
			foreach($matchts[1] as $key=>$tag)
			{
				$tags.=$tag.' ';
			}
		}
		$pubtime = date("Y-m-d",strtotime($matchd[1].'00'));
		$metas="发表日期：".$pubtime." 作者：".$matcha[1]."分类：".$matchc[1]." 标签： ".$tags;		
		$DH_output_content_each =  str_replace("%metas%",$metas,$DH_output_content);
		$article_index=article_index($matchb[1]);
		$DH_output_content_each =  str_replace("%entry%",$article_index,$DH_output_content_each);
		$DH_output_content_each =  str_replace("%title%",$matchT[1],$DH_output_content_each);
		$DH_output_content_each =  str_replace("%keywords%",$matchT[1],$DH_output_content_each);
		$DH_output_content_each =  str_replace("%description%",$matchT[1].' 详细页面',$DH_output_content_each);
		$DH_output_content_each =  str_replace("%cat%",$matchc[1],$DH_output_content_each);
		$urlcode = 'c'.str_replace("%",'',rawurlencode($matchc[1]));
		$caturl = $DH_index_url.$urlcode.'/1.html';
		$DH_output_content_each =  str_replace("%caturl%",$caturl,$DH_output_content_each);
		$DH_output_content_each =  str_replace("%id%",$i+$begincount,$DH_output_content_each);
		$DH_output_content_each =  str_replace("%tab%",'&nbsp;&nbsp;&nbsp;&nbsp;',$DH_output_content_each);
		
		$DH_output_file = output_page_path($DH_output_html_path,$i+$begincount);
		dh_file_put_contents($DH_output_file,$DH_output_content_each);		
	}
}

function dh_gen_list()
{
	global $lists,$DH_home_url,$DH_html_path,$DH_output_path,$DH_output_index_path,$DH_src_path;
	if (!file_exists($DH_output_index_path))  
		mkdir($DH_output_index_path,0777);
	
	$DH_input_html  = $DH_html_path . 'list.html';
	$DH_output_content = dh_file_get_contents("$DH_input_html");
	$DH_output_content = setshare($DH_output_content,'list.js');
	$DH_output_content = str_replace("%home%",$DH_home_url,$DH_output_content);
	
	$DH_input_html  = $DH_html_path . 'list_each.html';
	$listeach = dh_file_get_contents("$DH_input_html");	
	//echo $DH_output_content;
	
	$tags=array();
	$cats=array();
	$all=array();
	foreach($lists as $key=>$list)
	{
		preg_match('/<\_c>(.*?)<\/\_c>/s',$list,$matchc);
		if(!empty($matchc[1]))
		{
			//$urlcode = rawurlencode($matchc[1]);
			//$urlcode = 'c'.str_replace("%",'',$urlcode);
			//$urlcode = $matchc[1];
			if(empty($cats[$matchc[1]]))
			{
				$cats[$matchc[1]]=array($key);
			}
			else
			{
				array_push($cats[$matchc[1]],$key);
			}
			
			array_push($all,$key);		
		}		
		preg_match_all('/<\_t>(.*?)<\/\_t>/s',$list,$matchts);
		//print_r($matchts);
		if(!empty($matchts[1]))
		{
			foreach($matchts[1] as $tag)
			{
				//$urlcode = 't'.str_replace("%",'',rawurlencode($tag));
				//$urlcode = $tag;
				if(empty($tags[$tag]))
				{
					$tags[$tag]=array($key);
				}
				else
				{
					array_push($tags[$tag],$key);
				}
			}
		}	
	}
	
	foreach($tags as $key=>$tag)
	{
		$urlcode = 't'.str_replace("%",'',rawurlencode($key));
		dh_gen_each_list($tag,$urlcode,'标签 '.$key,$listeach,$DH_output_content);
	}	
	foreach($cats as $key=>$cat)
	{
		$urlcode = 'c'.str_replace("%",'',rawurlencode($key));
		dh_gen_each_list($cat,$urlcode,'分类 '.$key,$listeach,$DH_output_content);
	}	
	dh_gen_each_list($all,'all','最新更新',$listeach,$DH_output_content);
	//print_r($cats);
	//print_r($tags);
	//print_r($all);
}


function dh_gen_each_list($eachlist,$urlname,$name,$listeach,$content)
{
	global $DH_output_index_path,$lists,$lists_num,$pagecount,$DH_index_url,$DH_html_url;
	$liout="";
	$DH_output_file_dir = $DH_output_index_path.$urlname.'/';
	if (!file_exists($DH_output_file_dir))  
		mkdir($DH_output_file_dir,0777);
		
	$count_all=count($eachlist);
	echo $name.' 共'.$count_all."篇/".$pagecount;
	$pages=ceil($count_all/$pagecount);
	echo '/共'. $pages. "页</br>\n";	
	
	$count=0;
	foreach($eachlist as $key=>$list)
	{
		$count++;
		$onelist = $lists[$list];
		preg_match('/<\_T>(.*?)<\/\_T>/s',$onelist,$matchT);
		preg_match('/<\_b>\<!\-\-(.*?)\-\-\><\/\_b>/s',$onelist,$matchb);
		preg_match('/<\_d>(.*?)<\/\_d>/s',$onelist,$matchd);		
		preg_match('/<\_a>(.*?)<\/\_a>/s',$onelist,$matcha);
		preg_match('/<\_c>(.*?)<\/\_c>/s',$onelist,$matchc);
		preg_match_all('/<\_t>(.*?)<\/\_t>/s',$onelist,$matchts);
		//print_r($match);
		$tags='';
		if(!empty($matchts[1]))
		{
			foreach($matchts[1] as $key=>$tag)
			{
				$urlcode = 't'.str_replace("%",'',rawurlencode($tag));
				$tagurl = $DH_index_url.$urlcode.'/1.html';
				$tagseach="<a href=\"$tagurl\" title=\"$tag\">$tag</a>";
				$tags.=$tagseach.' ';
			}
		}
		
		$listtmp = str_replace("%title%",$matchT[1],$listeach);
		$listtmp = str_replace("%content%",$matchb[1],$listtmp);
		$listtmp = str_replace("%cat%",$matchc[1],$listtmp);
		$urlcode = 'c'.str_replace("%",'',rawurlencode($matchc[1]));
		$caturl = $DH_index_url.$urlcode.'/1.html';
		$listtmp = str_replace("%caturl%",$caturl,$listtmp);
		//echo $list.'-->'.$lists_num[$list]."\n";
		$html_url = output_page_path($DH_html_url,$lists_num[$list]);
		$listtmp = str_replace("%url%",$html_url,$listtmp);
		$listtmp = str_replace("%tags%",$tags,$listtmp);
		$listtmp = str_replace("%author%",$matcha[1],$listtmp);
		$time = strtotime($matchd[1].'00');
		$date=date("y-m",$time);
		//print_r($date);
		$datew=date("D",$time);
		$dated=date("d",$time);
		$listtmp = str_replace("%date%",$date,$listtmp);
		$listtmp = str_replace("%datew%",$datew,$listtmp);
		$listtmp = str_replace("%dated%",$dated,$listtmp);
		$listtmp =  str_replace("%id%",$lists_num[$list],$listtmp);
		
		$liout.=$listtmp;
		if($count%$pagecount==0)
		{
			$catpage = $count/$pagecount;
			$pagenavi = dh_pagenavi(5,$pages,$DH_index_url.$urlname.'/',$catpage);
			echo 'genpage:'.$catpage."</br>\n";				
			$content_new = str_replace("%pagenavi%",$pagenavi,$content);
			$content_new = str_replace("%list_each%",$liout,$content_new);
			$content_new = str_replace("%num%",$catpage,$content_new);
			
			$content_new =  str_replace("%cat%",$name,$content_new);
			$caturl = $DH_index_url.$urlname.'/1.html';
			$content_new =  str_replace("%caturl%",$caturl,$content_new);			
			$content_new =  str_replace("%page%",$catpage,$content_new);
			
			$DH_output_file = $DH_output_file_dir.$catpage.'.html';
			dh_file_put_contents($DH_output_file,$content_new);
			$liout='';
		}	
	}
	if($count%$pagecount!=0)
	{
		$catpage = ceil($count/$pagecount);
		$pagenavi = dh_pagenavi(5,$pages,$DH_index_url.$urlname.'/',$catpage);
		echo 'genpage:'.$catpage."</br>\n";				
		$content_new = str_replace("%pagenavi%",$pagenavi,$content);
		$content_new = str_replace("%list_each%",$liout,$content_new);
		$content_new = str_replace("%num%",$catpage,$content_new);
		
		$content_new =  str_replace("%cat%",$name,$content_new);
		$caturl = $DH_index_url.$urlname.'/1.html';
		$content_new =  str_replace("%caturl%",$caturl,$content_new);
		$content_new =  str_replace("%page%",$catpage,$content_new);
		
		$DH_output_file = $DH_output_file_dir.$catpage.'.html';
		dh_file_put_contents($DH_output_file,$content_new);
	}
	//print_r($list_all);
}

function output_all()
{
	global $lists,$DH_src_path,$begincount,$pages;
	$lists_all='';
	$i=1;
	foreach($lists as $key=>$list)
	{
		$lists_add = "\n<_e>\n<_i>".($i).'</_i>'.$list."\n</_e>";
		$lists_all .= $lists_add;
		$lists[$key]= $lists_add;
		$i++;
	}
	dh_file_put_contents($DH_src_path.'tmp/list.xml',$lists_all);

	$pages_all='';
	$i=1;
	foreach($pages as $key=>$page)
	{
		$pages_add = "\n<_e>\n<i>".($i+$begincount).'</_i>'.$page."\n</_e>";
		$pages_all .= $pages_add;
		$pages[$key]= $pages_add;
		$i++;
	}
	dh_file_put_contents($DH_src_path.'tmp/page.xml',$pages_all);	
}
?>