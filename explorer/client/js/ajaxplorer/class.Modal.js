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
 */
/**
 * Encapsulation of the lightbox script for modal windows. Used for alerting user, but
 * also for all popup forms (generic_dialog_box)
 * An instance is automatically created at the end of the file, no very clean, ajaxplorer
 * should create it instead.
 */
Class.create("Modal", {

	/**
	 * @var Boolean Current state of the page. If true, calls the updateLoadingProgress
	 */
	pageLoading: true,
	/**
	 * Constructor
	 */
	initialize: function(){
	},
	/**
	 * Find the forms
	 */
	initForms: function(){
		this.elementName = 'generic_dialog_box';
		this.htmlElement = $(this.elementName);
		this.dialogTitle = this.htmlElement.select(".dialogTitle")[0];
		this.dialogContent = this.htmlElement.select(".dialogContent")[0];
		this.currentForm;
		this.cachedForms = new Hash();
		this.iframeIndex = 0;	
	},
	
	/**
	 * Compute dialogContent html
	 * @param sTitle String Title of the popup
	 * @param sIconSrc String Source icon
	 */
	prepareHeader: function(sTitle, sIconSrc){
		var hString = "<span class=\"titleString\">";
		if(sIconSrc != "") hString = "<span class=\"titleString\"><img src=\""+sIconSrc.replace('22', '16')+"\" width=\"16\" height=\"16\" align=\"top\"/>&nbsp;";
		var closeBtn = '<img id="modalCloseBtn" style="cursor:pointer;float:right;margin-top:2px;" src="'+ajxpResourcesFolder+'/images/actions/16/window_close.png" />';  
		hString += sTitle + '</span>';
		this.dialogTitle.update(closeBtn + hString);
	},
	
	/**
	 * Shows a dialog box by getting the form from the hidden_forms
	 * @param sTitle String Title of the box
	 * @param sFormId String Id of the form to use as content
	 * @param fOnLoad Function Callback after the popup is shown, passe the form as argument
	 * @param fOnComplete Function Callback for OK button
	 * @param fOnCancel Function Callback for Cancel button
	 * @param bOkButtonOnly Boolean Wether to hide cancel button
	 * @param skipButtons Boolean Wether to hide all buttons
	 */
	showDialogForm: function(sTitle, sFormId, fOnLoad, fOnComplete, fOnCancel, bOkButtonOnly, skipButtons){
		this.clearContent(this.dialogContent);
		//this.dialogTitle.innerHTML = sTitle;
		var newForm;
		if($(sFormId).tagName == 'FORM') // WE PASSED A PREFETCHED HIDDEN FORM
		{
			newForm = $(sFormId);
			newForm.show();
		}
		else
		{
			var formDiv = $(sFormId);
			//var formDiv = $('all_forms').select('[id="'+sFormId+'"]')[0];	
			var newForm = document.createElement('form');
			newForm.id = 'modal_action_form';
			newForm.setAttribute('name','modal_action_form');
			newForm.setAttribute('action', 'cont.php');
			newForm.appendChild(formDiv.cloneNode(true));
			var reloadIFrame = null;
			if($(newForm).getElementsByTagName("iframe")[0])
			{
				reloadIFrame = $(newForm).getElementsByTagName("iframe")[0];
				reloadIFrameSrc = $(newForm).getElementsByTagName("iframe")[0].getAttribute("src");
			}
			if(formDiv.getAttribute('action'))
			{
				var actionField = document.createElement('input');
				actionField.setAttribute('type', 'hidden'); 
				actionField.setAttribute('name', 'get_action'); 
				actionField.setAttribute('value', formDiv.getAttribute('action'));
				newForm.appendChild(actionField);
			}		
		}
		if(!this.cachedForms.get(sFormId) && !skipButtons){
			this.addSubmitCancel(newForm, fOnCancel, bOkButtonOnly);
		}
		this.dialogContent.appendChild(newForm);
		var boxPadding = $(sFormId).getAttribute("box_padding");
		if(!boxPadding) boxPadding = 10;
		this.dialogContent.setStyle({padding:boxPadding});

		
		if(fOnCancel){
			this.dialogTitle.select('#modalCloseBtn')[0].observe("click", function(){fOnCancel(modal.getForm());hideLightBox();});
		}
		else{
			this.dialogTitle.select('#modalCloseBtn')[0].observe("click", function(){hideLightBox();});
		}			
		
		if(fOnComplete)	{
			newForm.onsubmit = function(){
				try{
					fOnComplete(modal.getForm());
				}catch(e){
					alert('Unexpected Error : please report!\n'+e);				
				}
				return false;
			};
		}
		else {
			newForm.onsubmit = function(){
				ajaxplorer.actionBar.submitForm(modal.getForm());
				hideLightBox();
				return false;
			};
		}
		this.showContent(this.elementName, $(sFormId).getAttribute("box_width"), $(sFormId).getAttribute("box_height"));
		if($(newForm).getElementsBySelector(".dialogFocus").length)
		{
			objToFocus = $(newForm).getElementsBySelector(".dialogFocus")[0];
			setTimeout('objToFocus.focus()', 500);
		}
		if($(newForm).getElementsBySelector(".replace_rep").length)
		{
			repDisplay = $(newForm).getElementsBySelector(".replace_rep")[0];
			repDisplay.innerHTML = ajaxplorer.getContextHolder().getContextNode().getPath();
		}
		if($(newForm).getElementsBySelector(".replace_file").length)
		{
			repDisplay = $(newForm).getElementsBySelector(".replace_file")[0];
			repDisplay.innerHTML = getBaseName(ajaxplorer.getUserSelection().getUniqueFileName());
		}
		if($(newForm).select('.dialogEnterKey').length && Prototype.Browser.IE){
			$(newForm).select('.dialogEnterKey').each(function(el){
				if(el.enterObserver) return;
				el.observe("keypress", function(event){
					if(event.keyCode == Event.KEY_RETURN){
						newForm.onsubmit();						
					}
				});
				el.enterObserver = true;
			});
		}
		this.currentForm = newForm;
		if(fOnLoad != null)
		{
			fOnLoad(this.currentForm);
			// Reload shadow if the content has changed after the fOnLoad call
			this.refreshDialogAppearance();
		}
		// SAFARI => FORCE IFRAME RELOADING
		if(Prototype.Browser.WebKit && reloadIFrame && reloadIFrameSrc) reloadIFrame.src = reloadIFrameSrc;
	},
	/**
	 * Utility for effectively showing the modal
	 * @param elementName String
	 * @param boxWidth String Width in pixel or in percent
	 * @param boxHeight String Height in pixel or in percent
	 * @param skipShadow Boolean Do not add a shadow
	 */
	showContent: function(elementName, boxWidth, boxHeight, skipShadow){
		ajaxplorer.disableShortcuts();
		ajaxplorer.disableNavigation();
		ajaxplorer.blurAll();
		var winWidth = $(document.body).getWidth();
		var winHeight = $(document.body).getHeight();
	
		// WIDTH / HEIGHT
		if(boxWidth != null){
			if(boxWidth.indexOf("%") ==-1 && parseInt(boxWidth) > winWidth){
				boxWidth = '90%';
			}
			if(boxWidth.indexOf('%') > -1){
				percentWidth = parseInt(boxWidth);
				boxWidth = parseInt((winWidth * percentWidth) / 100);
			}
			$(elementName).setStyle({width:boxWidth+'px'});
		}
		if(boxHeight != null){
			if(boxHeight.indexOf('%') > -1){
				percentHeight = parseInt(boxHeight);
				boxHeight = parseInt((winHeight * percentHeight) / 100);
			}
			$(elementName).setStyle({height:boxHeight+'px'});
		}else{
			if (Prototype.Browser.IE){	
				$(elementName).setStyle({height:'1%'});
			}else{
				$(elementName).setStyle({height:'auto'});
			}
		}
		
		this.refreshDialogPosition();
			
		displayLightBoxById(elementName);
		
		// FORCE ABSOLUTE FOR SAFARI
		$(elementName).style.position = 'absolute';
		// FORCE FIXED FOR FIREFOX
		if (Prototype.Browser.Gecko){					
			$(elementName).style.position = 'fixed';
		}
		else if(Prototype.Browser.IE){
			$$('select').invoke('show');
			// REFRESH PNG IMAGES FOR IE!
			refreshPNGImages(this.dialogContent);			
		}
		
		if(skipShadow) return;
		Shadower.shadow($(elementName), 
			{
				distance: 3,
				angle: 130,
				opacity: 0.5,
				nestedShadows: 3,
				color: '#000000',
				shadowStyle:{display:'block'}
			}, true);
				
	},
	/**
	 * Find an editor using the editorData and initialize it
	 * @param editorData Object
	 */
	openEditorDialog : function(editorData){
		if(!editorData.formId){
			ajaxplorer.displayMessage('ERROR', 'Error, you must define a formId attribute in your &lt;editor&gt; manifest (or set it as openable="false")');
			return;
		}
		var editorKlass = editorData.editorClass;
		modal.prepareHeader(editorData.text, resolveImageSource(editorData.icon, '/images/actions/ICON_SIZE', 16));
		var loadFunc = function(oForm){			
			if(typeof(editorKlass) == "string"){
				ajaxplorer.actionBar.editor = eval('new '+editorKlass+'(oForm)');
			}else{
				ajaxplorer.actionBar.editor = new editorKlass(oForm);
			}
			ajaxplorer.actionBar.editor.open(ajaxplorer.getUserSelection());
			//ajaxplorer.actionBar.editor.resize();
		};
		this.showDialogForm('', editorData.formId, loadFunc, null, null, true, true);			
	},
	/**
	 * Returns the current form, the real one.
	 * @returns HTMLForm
	 */
	getForm: function()	{
		return this.currentForm;
	},
	/**
	 * Refresh position after a window change
	 * @param checkHeight Boolean
	 * @param elementToScroll HTMLElement
	 */
	refreshDialogPosition: function(checkHeight, elementToScroll){
		var winWidth = $(document.body).getWidth();
		var winHeight = $(document.body).getHeight();
		boxWidth = $(this.elementName).getWidth();	
		var boxHeight = $(this.elementName).getHeight();
		
		if(checkHeight && boxHeight > parseInt(winHeight*90/100)){
			var maxHeight = parseInt(winHeight*90/100);
			var crtScrollHeight = elementToScroll.getHeight();
			var crtOffset = boxHeight - crtScrollHeight;
			if(maxHeight > crtOffset){ 
				elementToScroll.setStyle({
					overflow:'auto',
					height:(maxHeight-crtOffset)+'px'
				});		
				boxHeight = $(this.elementName).getHeight();
			}
		}
		// POSITION HORIZONTAL
		var offsetLeft = parseInt((winWidth - parseInt(boxWidth)) / 2);
		$(this.elementName).setStyle({left:offsetLeft+'px'});
		// POSITION VERTICAL
		var offsetTop = parseInt(((winHeight - boxHeight)/3));
		$(this.elementName).setStyle({top:offsetTop+'px'});		
	},
	/**
	 * Refresh appearance after the dialog box changed (shadow)
	 */
	refreshDialogAppearance:function(){
		Shadower.shadow($(this.elementName), 
			{
				distance: 4,
				angle: 130,
				opacity: 0.5,
				nestedShadows: 3,
				color: '#000000',
				shadowStyle:{display:'block'}
			}, true);		
	},
	/**
	 * Clear all content
	 * @param object HTMLElement The current form
	 */
	clearContent: function(object){
		// REMOVE CURRENT FORM, IF ANY
		if(object.getElementsBySelector("form").length)
		{
			var oThis = this;
			object.getElementsBySelector("form").each(function(currentForm){
				if(currentForm.target == 'hidden_iframe' || currentForm.id=='login_form' || currentForm.id=='user_pref_form'){
					currentForm.hide();
					oThis.cachedForms.set(currentForm.id,true);
				}
				else{
					try{object.removeChild(currentForm);}catch(e){}
				}
			});		
		}	
	},
	/**
	 * Adds buttons to the content
	 * @param oForm HTMLElement Current form
	 * @param fOnCancel Function Callback on cancel
	 * @param bOkButtonOnly Boolean Hide cancel
	 * @param position String Position.insert() allowed key.
	 * @returns HTMLElement
	 */
	addSubmitCancel: function(oForm, fOnCancel, bOkButtonOnly, position){
		var contDiv = new Element('div', {className:'dialogButtons'});
		var okButton = new Element('input', {
			type:'image',
			name:(bOkButtonOnly?'close':'ok'),
			src:ajxpResourcesFolder+'/images/actions/22/dialog_'+(bOkButtonOnly?'close':'ok_apply')+'.png',
			height:22,
			width:22,
			title:MessageHash[48]});
		okButton.addClassName('dialogButton');
		okButton.addClassName('dialogFocus');
		contDiv.insert(okButton);
		if(!bOkButtonOnly)
		{
			var caButton = new Element('input', {
				type:"image",
				name:"can",
				height:22,
				width:22,
				src:ajxpResourcesFolder+'/images/actions/22/dialog_close.png',
				title:MessageHash[49],
				className:"dialogButton"
			});
			if(fOnCancel){
				caButton.observe("click",function(e){fOnCancel(modal.getForm());hideLightBox();Event.stop(e);return false;});
			}
			else{
				caButton.observe("click",function(e){hideLightBox();Event.stop(e);return false;});
			}
			contDiv.insert(caButton);
		}	
		if(!position){
			position = 'bottom';
		}
		var obj = {}; 
		obj[position] = contDiv;
		$(oForm).insert(obj);
		oForm.hasButtons = true;
		return contDiv;
	},
	
	/**
	 * Create a simple tooltip
	 * @param element HTMLElement
	 * @param title String
	 */
	simpleTooltip : function(element, title){
		element.observe("mouseover", function(event){
			var x = Event.pointerX(event)+10;
			var y = Event.pointerY(event)+10;
			if(!this.tooltip){
				this.tooltip = new Element("div", {className:"simple_tooltip"});
				$$('body')[0].insert(this.tooltip);
			}
			this.tooltip.update(title);
			this.tooltip.setStyle({top:y,left:x});
			if(this.tipTimer){
				window.clearTimeout(this.tipTimer);
			}
			this.tooltip.show();
		}.bind(this) );
		element.observe("mouseout", function(event){
			if(!this.tooltip) return;
			this.tipTimer = window.setTimeout(function(){
				this.tooltip.hide();
			}.bind(this), 300);
		}.bind(this) );
	},
	/**
	 * Close the Message
	 */
	closeMessageDiv: function(){
		if(this.messageDivOpen)
		{
			new Effect.Fade(this.messageBox);
			this.messageDivOpen = false;
		}
	},
	/**
	 * Timer for automatically closing the message
	 */
	tempoMessageDivClosing: function(){
		this.messageDivOpen = true;
		setTimeout('modal.closeMessageDiv()', 6000);
	},
	/**
	 * Display a user message (notice or error)
	 * @param messageType String ERROR or SUCCESS
	 * @param message String Content of the message
	 */
	displayMessage: function(messageType, message){
		if(!this.messageBox){
			this.messageBox = new Element("div", {title:MessageHash[98],id:"message_div",className:"messageBox"});
			$(document.body).insert(this.messageBox);
			this.messageContent = new Element("div", {id:"message_content"});
			this.messageBox.update(this.messageContent);
			this.messageBox.observe("click", this.closeMessageDiv.bind(this));
		}
		message = message.stripScripts();
		message = message.replace(new RegExp("(\\n)", "g"), "<br>");
		if(messageType == "ERROR"){ this.messageBox.removeClassName('logMessage');  this.messageBox.addClassName('errorMessage');}
		else { this.messageBox.removeClassName('errorMessage');  this.messageBox.addClassName('logMessage');}
		this.messageContent.update(message);
		var container = $('content_pane');
		if(!container){
			container = $(ajxpBootstrap.parameters.get("MAIN_ELEMENT"));
		}
		var containerOffset = Position.cumulativeOffset(container);
		var containerDimensions = container.getDimensions();
		var boxHeight = $(this.messageBox).getHeight();
		var topPosition = containerOffset[1] + containerDimensions.height - boxHeight - 20;
		var boxWidth = parseInt(containerDimensions.width * 90/100);
		var leftPosition = containerOffset[0] + parseInt(containerDimensions.width*5/100);
		this.messageBox.setStyle({
			top:topPosition+'px',
			left:leftPosition+'px',
			width:boxWidth+'px'
		});
		new Effect.Corner(this.messageBox,"5px");
		new Effect.Appear(this.messageBox);
		this.tempoMessageDivClosing();
	},
	/**
	 * Bootloader helper. Sets total steps
	 * @param count Integer
	 */
	setLoadingStepCounts: function(count){
		this.loadingStepsCount = count;
		this.loadingStep = count;
	},
	
	/**
	 * Bootload helper. Increment total steps 
	 * @param add Integer
	 */
	incrementStepCounts: function(add){
		this.loadingStepsCount += add;
		this.loadingStep += add;
	},
	/**
	 * Bootloader helper
	 * @param state Integer Current loading step
	 */
	updateLoadingProgress: function(state){	
		this.loadingStep --;
		var percent = (1 - (this.loadingStep / this.loadingStepsCount));
		if(window.loaderProgress){
			window.loaderProgress.setPercentage(parseInt(percent)*100, false);
		}
		if(state && $('progressState')){
			$('progressState').update(state);
		}
		if(this.loadingStep == 0){
			this.pageLoading = false;
		}
		return;
	},
	/**
	 * Callback to be called on close
	 * @param func Function
	 */
	setCloseValidation : function(func){
		this.closeValidation = func;
	},
	
	/**
	 * Callback to be called on close
	 * @param func Function
	 */
	setCloseAction: function(func){
		this.closeFunction = func;
	},
	
	/**
	 * Close action. Remove shadow if any, call close callback if any.
	 */
	close: function(){	
		Shadower.deshadow($(this.elementName));
		if(this.closeFunction){
			 this.closeFunction();
			 //this.closeFunction = null;
		}
	}
});
	
var modal = new Modal();
