/*
 * Copyright 2007-2011 Charles du Jeu
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
 */
/**
 * Abstract implementation of an Editor. All editors should extend this one, as it provides
 * standard features for parsing actions, entering/exiting fullscreen, etc.. Events are triggered
 * at various moments of the editors lifecycle.
 * 
 * @package info.ajaxplorer.plugins 
 */
Class.create("AbstractEditor" , {
	
	/**
	 * @var Hash The default actions, initialized with fs, nofs and close
	 */
	defaultActions : new Hash(),
	/**
	 * @var String
	 */
	toolbarSeparator : '<div class="separator"></div>',
	/**
	 * @var Boolean Current state of the editor
	 */
	fullScreenMode : false,
	/**
	 * @var Hash For the moment supported options are "fullscreen", "closable", "floatingToolbar".
	 */
	editorOptions : new Hash({"fullscreen":true, "closable":true, "floatingToolbar":false}),
	
	/**
	 * Standard contructor
	 * @param oContainer Element dom not to attach to
	 */
	initialize : function(oContainer){
		this.element =  $(oContainer);
		this.defaultActions = new Hash({
			'fs' : '<a id="fsButton"><img src="'+ajxpResourcesFolder+'/images/actions/22/window_fullscreen.png"  width="22" height="22" alt="" border="0"><br><span message_id="235"></span></a>',
			'nofs' : '<a id="nofsButton" style="display:none;"><img src="'+ajxpResourcesFolder+'/images/actions/22/window_nofullscreen.png"  width="22" height="22" alt="" border="0"><br><span message_id="236"></span></a>',
			'close':'<a id="closeButton"><img src="'+ajxpResourcesFolder+'/images/actions/22/fileclose.png"  width="22" height="22" alt="" border="0"><br><span message_id="86"></span></a>'
		});		
		this.createTitleSpans();
		this.initActions();
		modal.setCloseAction(function(){this.close();}.bind(this));
	},
	
	/**
	 * Initialize standards editor actions
	 */
	initActions : function(){
		this.actions = new Hash();
		var actionBarSel = this.element.select('.action_bar');		
		if(!actionBarSel.length){
			this.actionBar = new Element('div', {className:'action_bar'});
			this.element.insert({top:this.actionBar});
		}else{
			this.actionBar = actionBarSel[0];
		}
		if(!this.editorOptions.get("fullscreen")){
			this.defaultActions.unset("fs");
			this.defaultActions.unset("nofs");
		}
		this.actionBar.insert({top:this.toolbarSeparator});	
		this.actionBar.insert({bottom:this.toolbarSeparator});
		this.actionBar.insert({bottom:this.defaultActions.values().join('\n')});
		this.actionBar.select('a').each(function(link){
			link.onclick = function(){return false;};
			link.href = "#";
			var span = link.select('span[message_id]')[0];			
			if(span) span.update(MessageHash[span.readAttribute("message_id")]);
			this.actions.set(link.id, link);
		}, this);
		
		if(this.actions.get("closeButton")){
			this.actions.get("closeButton").observe("click", function(){
				hideLightBox(true);
			}.bind(this) );
			modal.setCloseValidation(function(){
				if(this.isModified && !window.confirm(MessageHash[201])){
					return false;
				}
				return true;
			}.bind(this) );			
			if(window.ajxpMobile){
				// Make sure "Close" is the first.
				this.actionBar.insert({top:this.actions.get("closeButton")});
			}
		}
		if(this.actions.get("fsButton")){
			this.actions.get("fsButton").observe("click", this.setFullScreen.bind(this));
			this.actions.get("nofsButton").observe("click", this.exitFullScreen.bind(this));
			this.actions.get("fsButton").show();
			this.actions.get("nofsButton").hide();
		}
		
		if(this.editorOptions.floatingToolbar){
			this.makeToolbarFloatable();
		}
		
		attachMobileScroll(this.actionBar, "horizontal");
		
	},
	
	/**
	 * Experimental : detach toolbar
	 */
	makeToolbarFloatable : function(){
		this.actionBar.absolutize();
		this.actionBar.setStyle({
			zIndex:(parseInt(this.element.getStyle("zIndex")) + 1000),
			width : '',
			top: '',
			bottom : 20,
			left : '30%'
		});
	},
	
	/**
	 * Creates the title label depending on the "modified" status
	 */
	createTitleSpans : function(){
		var crtTitle = $(modal.dialogTitle).select('span.titleString')[0];
		this.filenameSpan = new Element("span", {className:"filenameSpan"});
		crtTitle.insert({bottom:this.filenameSpan});
		
		this.modifSpan = new Element("span", {className:"modifiedSpan"});
		crtTitle.insert({bottom:this.modifSpan});		
		
	},
	
	/**
	 * Opens the editor with the current model
	 * @param userSelection AjxpDataModel the data model
	 */
	open : function(userSelection){
		this.userSelection = userSelection;
	},
	/**
	 * Updates the editor title
	 * @param title String
	 */
	updateTitle : function(title){
		if(title != ""){
			title = " - " + title;
		}
		this.filenameSpan.update(title);
		if(this.fullScreenMode){
			this.refreshFullScreenTitle();
		}
	},
	/**
	 * Change editor status
	 * @param isModified Boolean
	 */
	setModified : function(isModified){
		this.isModified = isModified;
		this.modifSpan.update((isModified?"*":""));
		if(this.actions.get("saveButton")){
			if(isModified){
				this.actions.get("saveButton").removeClassName("disabled");
			}else{
				this.actions.get("saveButton").addClassName("disabled");
			}
		}
		if(this.fullScreenMode){
			this.refreshFullScreenTitle();
		}
		this.element.fire("editor:modified", isModified);
	},
	/**
	 * Switch to fullscreen mode
	 */
	setFullScreen : function(){
		this.element.fire("editor:enterFS");
		if(!this.contentMainContainer){
			this.contentMainContainer = this.element;
		}
		this.originalHeight = this.contentMainContainer.getHeight();	
		this.originalWindowTitle = document.title;
		this.element.absolutize();
		this.actionBar.setStyle({marginTop: 0});
		$(document.body).insert(this.element);
		this.element.setStyle({
			top:0,
			left:0,
			marginBottom:0,
			backgroundColor:'#fff',
			width:'100%',
			height:document.viewport.getHeight(),
			zIndex:3000});
		this.actions.get("fsButton").hide();
		this.actions.get("nofsButton").show();
		this.fullScreenListener = function(){
			this.element.setStyle({height:document.viewport.getHeight()});
			this.resize();		
		}.bind(this);
		Event.observe(window, "resize", this.fullScreenListener);
		this.refreshFullScreenTitle();
		this.resize();
		this.fullScreenMode = true;
		this.element.fire("editor:enterFSend");
	},
	/**
	 * Exits fullscreen mode
	 */
	exitFullScreen : function(){
		if(!this.fullScreenMode) return;
		this.element.fire("editor:exitFS");
		Event.stopObserving(window, "resize", this.fullScreenListener);
		this.element.relativize();
		$$('.dialogContent')[0].insert(this.element);
		this.element.setStyle({top:0,left:0,zIndex:100});		
		this.resize(this.originalHeight);
		this.actions.get("fsButton").show();
		this.actions.get("nofsButton").hide();		
		document.title = this.originalWindowTitle;
		this.fullScreenMode = false;
		this.element.fire("editor:exitFSend");
	},
	/**
	 * Resizes the main container
	 * @param size int|null
	 */
	resize : function(size){
		if(size){
			this.contentMainContainer.setStyle({height:size});
		}else{
			fitHeightToBottom(this.contentMainContainer, this.element);
		}
		this.element.fire("editor:resize", size);
	},
	/**
	 * Closes the editor
	 * @returns Boolean
	 */
	close : function(){		
		if(this.fullScreenMode){
			this.exitFullScreen();
		}
		this.element.fire("editor:close");
		modal.setCloseAction(null);
		return false;
	},
	
	/**
	 * Refreshes the title
	 */
	refreshFullScreenTitle : function(){
		document.title = "AjaXplorer - "+$(modal.dialogTitle).innerHTML.stripTags().replace("&nbsp;","");
	},
	/**
	 * Add a loading image to the given element
	 * @param element Element dom node
	 */
	setOnLoad : function(element){	
		addLightboxMarkupToElement(element);
		var img = document.createElement("img");
		img.src = ajxpResourcesFolder+"/images/loadingImage.gif";
		$(element).select("#element_overlay")[0].appendChild(img);
		this.loading = true;
	},
	/**
	 * Removes the image from the element 
	 * @param element Element dom node
	 */
	removeOnLoad : function(element){
		removeLightboxFromElement(element);
		this.loading = false;	
	},

	/**
	 * Called by the other components to create a preview (thumbnail) of a given node
	 * @param ajxpNode AjxpNode The node to display
	 * @param rich Boolean whether to display a rich content (flash, video, etc...) or not (image)
	 * @returns Element
	 */
	getPreview : function(ajxpNode, rich){
		// Return icon if not overriden by derived classes
		src = AbstractEditor.prototype.getThumbnailSource(ajxpNode);
		imgObject = new Element("img", {src:src, width:64, height:64, align:'absmiddle', border:0});
		imgObject.resizePreviewElement = function(dimensionObject){
			dimensionObject.maxWidth = dimensionObject.maxHeight = 64;
			var styleObject = fitRectangleToDimension({width:64,height:64},dimensionObject);
			if(dimensionObject.width >= 64){
				var newHeight = parseInt(styleObject.height);
				var mT = parseInt((dimensionObject.width - 64)/2) + dimensionObject.margin;
				var mB = dimensionObject.width+(dimensionObject.margin*2)-newHeight-mT-1;
				styleObject.marginTop = mT + "px"; 
				styleObject.marginBottom = mB + "px"; 
			}
			this.setStyle(styleObject);
		}.bind(imgObject);
		return imgObject;
	},
	
	/**
	 * Gets the standard thumbnail source for previewing the node
	 * @param ajxpNode AjxpNode
	 * @returns String
	 */
	getThumbnailSource : function(ajxpNode){
		return resolveImageSource(ajxpNode.getIcon(), "/images/mimes/ICON_SIZE", 64);
	}
	
});