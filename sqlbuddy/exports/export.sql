--
-- MySQL 5.1.41
-- Mon, 21 Nov 2011 11:00:26 +0000
--

CREATE TABLE `grids` (
   `grid_id` int(11) not null auto_increment,
   `grid_name` varchar(60),
   `grid_width` int(3) default '520',
   `grid_height` varchar(3) default '300',
   `grid_content` text,
   `grid_type` text,
   PRIMARY KEY (`grid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=11;

INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('1', 'Photos II', '260', '300', '', '');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('2', 'Photos I', '260', '300', '', '');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('3', 'Adsense', '520', '100', '', '');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('4', 'Twitter', '520', '300', 'content/counter.php', 'component');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('5', 'Main Counter', '520', '104', 'apps/counter.php', 'component');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('6', 'Image 520x150', '520', '160', 'content/520x150.png', 'picture');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('7', 'Footer', '520', '600', 'content/footer.png', 'picture');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('8', 'All Likes', '520', '220', '<div id=\"fb-root\"></div>\n<script>(function(d, s, id) {\n  var js, fjs = d.getElementsByTagName(s)[0];\n  if (d.getElementById(id)) {return;}\n  js = d.createElement(s); js.id = id;\n  js.src = \"//connect.facebook.net/en_US/all.js#xfbml=1&appId=135219336580217\";\n  fjs.parentNode.insertBefore(js, fjs);\n}(document, \'script\', \'facebook-jssdk\'));</script>\n<style>\n.section_title {line-height:32px;background-color:#eceff4;font-size:13px;font-weight:bold;width:510px; font-family: verdana; margin:0 auto;border-top:1px solid #93a3c4;padding-left:10px;position:relative;}\n#all_likes {width:520px;margin:0 auto;}\n#all_likes .like_item {border-bottom:1px solid #ddd;}\n#all_likes .like_item .page_image {width:50px;padding-right:10px;padding-top:5px;padding-left:6px;}\n#all_likes .like_item .page_image div.picWrapper {height:50px;width:50px;position:relative;overflow:hidden;/*border:1px solid #B3B3B3;*/}\n#all_likes .like_item .page_image div.picWrapper img {}\n#all_likes .like_item .page_info {width:310px;}\n#all_likes .like_item .page_info a.page_title:link,\n#all_likes .like_item .page_info a.page_title:visited {line-height:26px;color:#526bae;font-size:14px;font-weight:bold;text-decoration:none; font-family:verdana}\n#all_likes .like_item .like_info {padding:10px 10px 10px 10px;}\n#all_likes .like_item .like_info .friend {float:left;margin:2px;margin-top:4px;height:40px;width:40px;overflow:hidden;}\n\n\n</style>\n<div class=\"section_title\" id=\"initial_header\">Diventa fan e ricevi notizie in anteprima dalle tue pagine preferite!</div>\n<div id=\"all_likes\">\n		<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" id=\"all_likes_table\">\n\n<tr class=\"like_item\" style=\"display: block; \">\n<td class=\"page_image\" valign=\"middle\"><div class=\"picWrapper\"><img width=\"50\" src=\"content/small.png\"></div></td>\n<td class=\"page_info\" valign=\"middle\"><a class=\"page_title\" target=\"_parent\" href=\"http://www.facebook.com/yahoo\">PagineGialle</a></td>\n<td class=\"like_info\" valign=\"middle\"><div class=\"fb-like\" data-href=\"http://www.facebook.com/paginegialle\" data-send=\"false\" data-width=\"290\" data-show-faces=\"true\"></div></td></tr>\n\n<tr class=\"like_item\" style=\"display: block; \"><td class=\"page_image\" valign=\"middle\"><div class=\"picWrapper\"><img width=\"50\" src=\"content/small.png\"></div></td>\n<td class=\"page_info\" valign=\"middle\"><a class=\"page_title\" target=\"_parent\" href=\"http://www.facebook.com/yahoonews\">TuttoCitta.it</a></td>\n<td class=\"like_info\" valign=\"middle\"><div class=\"fb-like\" data-href=\"http://www.facebook.com/paginegialle\" data-send=\"false\" data-width=\"290\" data-show-faces=\"true\"></div></td></tr>\n\n\n		\n	</div>', 'custom');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('9', 'Video', '520', '390', '<iframe width=\"520\" height=\"382\" src=\"http://www.youtube.com/embed/t_bSx-PrD6k\" frameborder=\"0\" allowfullscreen></iframe>', 'custom');
INSERT INTO `grids` (`grid_id`, `grid_name`, `grid_width`, `grid_height`, `grid_content`, `grid_type`) VALUES ('10', 'Search', '520', '520', 'apps/search.php', 'component');

CREATE TABLE `layouts` (
   `layout_id` int(11) not null,
   `layout_name` varchar(30),
   PRIMARY KEY (`layout_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `layouts` (`layout_id`, `layout_name`) VALUES ('1', 'Sample Layout');

CREATE TABLE `logon` (
   `userid` int(11) not null auto_increment,
   `useremail` varchar(50) not null,
   `password` varchar(50) not null,
   `userlevel` int(1) not null default '0',
   `name` varchar(20) default 'user',
   `surname` varchar(20),
   PRIMARY KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=3;

INSERT INTO `logon` (`userid`, `useremail`, `password`, `userlevel`, `name`, `surname`) VALUES ('1', 'vitally.marinchenko@gmail.com', '8a515872e2a7d7cdc3befdeb007ad4d5', '1', 'Vitally', 'Marinchenko');
INSERT INTO `logon` (`userid`, `useremail`, `password`, `userlevel`, `name`, `surname`) VALUES ('2', 'root', '63a9f0ea7bb98050796b649e85481845', '1', 'Admin', '');

CREATE TABLE `usergrid_relation` (
   `ug_id` int(11) not null auto_increment,
   `user_id_fk` int(11),
   `grid_id_fk` int(11),
   `grid_order` int(11),
   `layout_id` int(11),
   `grid_status` int(1),
   PRIMARY KEY (`ug_id`),
   KEY `user_id_fk` (`user_id_fk`),
   KEY `grid_id_fk` (`grid_id_fk`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=15;

INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('1', '1', '1', '2', '1', '0');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('2', '1', '2', '1', '1', '0');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('3', '1', '3', '0', '1', '0');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('4', '1', '4', '4', '1', '0');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('5', '1', '5', '6', '1', '1');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('6', '1', '6', '3', '1', '1');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('7', '1', '7', '9', '1', '1');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('8', '1', '8', '7', '1', '1');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('9', '1', '9', '5', '1', '1');
INSERT INTO `usergrid_relation` (`ug_id`, `user_id_fk`, `grid_id_fk`, `grid_order`, `layout_id`, `grid_status`) VALUES ('10', '1', '10', '8', '1', '1');