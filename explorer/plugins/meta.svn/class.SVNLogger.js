/**
 * @package info.ajaxplorer.plugins
 * 
 * Copyright 2007-2009 Charles du Jeu
 * This file is part of AjaXplorer.
 * The latest code can be found at http://www.ajaxplorer.info/
 * 
 * This program is published under the LGPL Gnu Lesser General Public License.
 * You should have received a copy of the license along with AjaXplorer.
 * 
 * The main conditions are as follow : 
 * You must conspicuously and appropriately publish on each copy distributed 
 * an appropriate copyright notice and disclaimer of warranty and keep intact 
 * all the notices that refer to this License and to the absence of any warranty; 
 * and give any other recipients of the Program a copy of the GNU Lesser General 
 * Public License along with the Program. 
 * 
 * If you modify your copy or copies of the library or any portion of it, you may 
 * distribute the resulting library provided you do so under the GNU Lesser 
 * General Public License. However, programs that link to the library may be 
 * licensed under terms of your choice, so long as the library itself can be changed. 
 * Any translation of the GNU Lesser General Public License must be accompanied by the 
 * GNU Lesser General Public License.
 * 
 * If you copy or distribute the program, you must accompany it with the complete 
 * corresponding machine-readable source code or with a written offer, valid for at 
 * least three years, to furnish the complete corresponding machine-readable source code. 
 * 
 * Any of the above conditions can be waived if you get permission from the copyright holder.
 * AjaXplorer is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * Description : Simple display of SVN logs.
 */
Class.create("SVNLogger", {
	initialize:function(form){
		this.element = form.select('[id="svnlog_box"]')[0];
		this.container = new Element('div', {
			style:"height:310px;overflow:auto;"
		});
		this.element.up('div.dialogContent').setStyle({padding:0});
		this.element.insert(this.container);	
		this.template = new Template('<div style="padding: 0px; border: 1px solid rgb(204, 204, 204); margin: 5px 8px 8px 4px;"><div style="padding: 3px; background-color: rgb(238, 238, 238);#{cssStyle}"><b>#{dateString} :</b> #{date} &nbsp;&nbsp;&nbsp;&nbsp;<b>#{revString} :</b> #{revision} &nbsp;&nbsp;&nbsp;&nbsp;<b>#{authString} :</b> #{author}<br></div><div style="padding: 3px;color:#333;word-wrap:break-word;">#{message}</div><div style="text-align: right; padding: 3px;">#{downloadLink}</div></div>');
		this.downloadTemplate = new Template('<a style="color:#79f;font-weight:bold;" ajxp_download="'+window.ajxpServerAccessPath+'&get_action=svndownload&file=#{fileName}&revision=#{revision}" href="#">#{downloadString}</a>');
		this.switchTemplate = new Template('<a style="color:#79f;font-weight:bold;" ajxp_url="'+window.ajxpServerAccessPath+'&get_action=svnswitch&revision=#{revision}" href="#">#{switchString}</a>');
		this.revMessage = MessageHash[243];
		this.authorMessage = MessageHash[244];
		this.dateMessage = MessageHash[245];
		this.messMessage = MessageHash[246];
		this.downMessage = MessageHash[88];
		this.switchMessage = MessageHash['meta.svn.3'];
		if(!$('svndownload_iframe')){
			$('hidden_frames').insert('<iframe id="svndownload_iframe" name="svndownload_iframe" style="display:none"></iframe>');
		}
	},
	
	open: function(currentRep){
		var selection = ajaxplorer.getUserSelection();
		if(currentRep || selection.isEmpty()){
			var ajxpNode = ajaxplorer.getContextNode();
		}else{
			var ajxpNode = selection.getUniqueNode();
		}
		this.fileName = ajxpNode.getPath();
		this.isFile = ajxpNode.isLeaf();
		var connexion = new Connexion();
		connexion.addParameter('get_action', 'svnlog');
		connexion.addParameter('file', this.fileName);
		connexion.onComplete = this.displayResponse.bind(this);
		this.setOnLoad();
		connexion.sendAsync();
	},
	
	addEntry:function(revision,author,date,message){
		var separator = '||';
		if(message.indexOf(separator)>-1){
			var split = message.split(separator);			
			author = split[1];
			message = "<b>"+split[2].toUpperCase()+"</b>";
			if(split[3]){
				message += " : "+split[3];
			}
		}
		
		var dateParts = date.split('\.');
		date = dateParts[0].replace("T", " ");
		
		var skipSwitch = false;
		var cssStyle = '';
		if(this.currentRev){
			if(this.currentRev.firstChild.nodeValue == revision){
				skipSwitch = true;
				cssStyle = "background-color:#efe;";
			}
		}else if(this.revisionRange){
			var start = parseInt(this.revisionRange.getAttribute('start'));
			var end = parseInt(this.revisionRange.getAttribute('end'));
			var rev = parseInt(revision);
			if(start <= rev && rev <= end){
				cssStyle = "background-color:#fee;";
			}
		}
		
		var dLink = (this.isFile?this.downloadTemplate.evaluate({
			fileName:this.fileName, 
			revision:revision,
			downloadString:this.downMessage
		}):(!skipSwitch?this.switchTemplate.evaluate({
			revision:revision,
			switchString:this.switchMessage
		}):''));
		this.container.insert(this.template.evaluate({
			revision:revision,
			author:author,
			date:date,
			message:message,
			downloadLink:dLink,			
			cssStyle:cssStyle,
			revString:this.revMessage,
			authString:this.authorMessage,
			dateString:this.dateMessage,
			messString:this.messMessage
		}));
	},
	
	displayResponse: function(transport){
		var oXmlDoc = transport.responseXML;
		if(oXmlDoc == null || oXmlDoc.documentElement == null) return;
		var root = oXmlDoc.documentElement;
		if(!root.childNodes.length) return;
		var logEntries = XPathSelectNodes(root, "log/logentry");
		this.currentRev = XPathSelectSingleNode(root, "current_revision");
		this.revisionRange = XPathSelectSingleNode(root, "revision_range");
		try{
			for(var i=0; i<logEntries.length;i++){
				var entry = logEntries[i];
				var revision = entry.getAttribute("revision");
				var author;var date;var message;
				for(var j=0;j<entry.childNodes.length;j++){
					if(entry.childNodes[j].nodeName == 'author'){
						author = entry.childNodes[j].firstChild.nodeValue;
					}else if(entry.childNodes[j].nodeName == 'date'){
						date = entry.childNodes[j].firstChild.nodeValue;
					}else if(entry.childNodes[j].nodeName == 'msg'){
						message = entry.childNodes[j].firstChild.nodeValue;
					}
				}			
				this.addEntry(revision,author,date,message);
			}
		}catch(e){
			ajaxplorer.displayMessage("ERROR", e.description + "(current index : "+i+")");
		}finally{
			this.removeOnLoad();
		}
		this.container.select("a").invoke("observe", "click", function(e){
			var a = e.findElement();
			if(a.getAttribute("ajxp_url")){
				var conn = new Connexion(a.getAttribute("ajxp_url"));
				conn.onComplete = function(){
					ajaxplorer.fireContextRefresh()
					hideLightBox();
				};
				conn.sendAsync();
			}else if(a.getAttribute("ajxp_download")){
				$('svndownload_iframe').src = a.getAttribute("ajxp_download");
			}
			Event.stop(e);
		});		
	},
	close:function(){
		
	},
	setOnLoad:function(){
		addLightboxMarkupToElement(this.container);
		var img = new Element("img", {
			src:ajxpResourcesFolder+'/images/loadingImage.gif',
			style:'margin-top:80px;'
		});
		this.container.down("#element_overlay").insert(img);		
	},
	removeOnLoad:function(){
		removeLightboxFromElement(this.container);
	}
});