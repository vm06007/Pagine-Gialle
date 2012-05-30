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
 * Description : A fully functionnal manager for the whole "Admin" driver
 */
ConfigEditor = Class.create({

	initialize: function(oForm){
		if(oForm) this.form = oForm;
	},
	
	setForm : function(oForm){
		this.form = oForm;
	},	
	
	
	/*************************************/
	/*       USERS FUNCTIONS             */
	/*************************************/			
	loadUser: function(userId, reload){

		if(!reload){
			var fieldset = this.form.down('fieldset');
			var legend = this.createTabbedFieldset(MessageHash['ajxp_conf.77'], fieldset.down('#user_acl'), MessageHash['ajxp_conf.78'], fieldset.down('#user_personal'));
			fieldset.insert({top:legend});		
		}
		this.roleId = null;
		this.userId = userId;
		var params = new Hash();
		params.set("get_action", "edit");
		params.set("sub_action", "edit_user");
		params.set("user_id", userId);
		var connexion = new Connexion();
		connexion.setParameters(params);
		connexion.onComplete = function(transport){
			if(reload){
				this.clearUserForm();
			}
			this.feedUserForm(transport.responseXML);			
			modal.refreshDialogPosition();
			modal.refreshDialogAppearance();
			ajaxplorer.blurAll();
		}.bind(this);
		connexion.sendAsync();		
	},	
	
	loadUsers : function(selection){	
		this.roleId = null;
		this.userId = null;
			
		this.form.down('#rights_pane').remove();
		this.form.down('#rights_legend').remove();
		this.form.down('#roles_pane').select('.dialogLegend')[0].update(MessageHash['ajxp_conf.83']);
		this.form.down('#roles_pane').select('span')[1].update(MessageHash['ajxp_conf.84']);
		var url = window.ajxpServerAccessPath + '&get_action=batch_users_roles';
		this.selectionUrl = selection.updateFormOrUrl(null, url);
		var connexion = new Connexion(this.selectionUrl);
		connexion.onComplete = function(transport){			
			this.populateRoles(transport.responseXML);
		}.bind(this);
		connexion.sendAsync();
	},
	
	clearUserForm : function(){
		this.clearRolesForm();
		this.clearRightsForm();
		this.clearWallets();
	},
	
	clearRolesForm :function(){
		var rolesPane = this.form.down('[id="roles_pane"]');
		var availSelect = rolesPane.down('div#available_roles');
		var userSelect = rolesPane.down('div#user_roles');
		Droppables.remove(availSelect);
		Droppables.remove(userSelect);
		if(this.draggables) this.draggables.invoke('destroy');
		availSelect.childElements().invoke("remove");
		userSelect.childElements().invoke("remove");	
	},
	
	clearRightsForm : function(){		
		var rightsPane = this.form.down('#rights_pane');
		rightsPane.setStyle({height: rightsPane.getHeight() + 'px'});
		rightsPane.select('tr').invoke("remove");
		rightsPane.down('tBody').insert('<tr id="loading_row"><td colspan="2">Reloading...</td></tr>');
	},
	
	clearWallets : function(){
		this.form.select('#wallets_pane')[0].childElements().invoke("remove");
	},
	
	feedUserForm : function(xmlData){
				
		var editPass = XPathGetSingleNodeText(xmlData, "admin_data/edit_options/@edit_pass")=="1";
		var editAdminRight = XPathGetSingleNodeText(xmlData, "admin_data/edit_options/@edit_admin_right")=="1";
		var editDelete = XPathGetSingleNodeText(xmlData, "admin_data/edit_options/@edit_delete")=="1";
		var adminStatus = (XPathGetSingleNodeText(xmlData, "admin_data/user/special_rights/@is_admin") == "1");		
						
		this.populateRoles(xmlData);
		
		this.generateRightsTable(xmlData);
				
		var passwordPane = this.form.select('[id="password_pane"]')[0];
		if(!editPass){
			passwordPane.hide();
		}else{
			var passButton = passwordPane.select('input.dialogButton')[0];
			passButton.observe('click', this.changePassword.bind(this));
			var passField = passwordPane.down('input[id="new_pass"]');
			var strength = new Protopass(passField, {
				barContainer:passwordPane.down('div[id="strength_container"]'),
				barPosition:'bottom',
				labelWidth: 27
			});
			strength.observeOnce("strength_change", function(){
				passwordPane.down('div[id="pass_subblock"]').setStyle({height:'43px'});
				modal.refreshDialogAppearance();
			});
		}
		
		if(!editAdminRight){
			this.form.select('[id="admin_right_pane"]')[0].hide();
		}else{
			var adminButton = this.form.select('[id="admin_rights"]')[0];
			adminButton.checked = adminStatus;
			adminButton.observe('click', function(){
				this.changeAdminRight(adminButton);
			}.bind(this));			
		}		
		
		this.generateCustomPane(xmlData);
		this.generateWalletsPane(xmlData);
	},
	
	populateRoles : function(xmlData){
		var rolesPane = this.form.down('[id="roles_pane"]');
		var availableRoles = XPathSelectNodes(xmlData, "admin_data/ajxp_roles/role");
		var userRoles = XPathSelectNodes(xmlData, "admin_data/user/ajxp_roles/role");
		var availSelect = rolesPane.down('div#available_roles');
		var userSelect = rolesPane.down('div#user_roles');
		var rolesId = $A();
		userRoles.each(function(xmlElement){
			var id = xmlElement.getAttribute('id');
			var option = new Element('div', {id:id, className:'ajxp_role user_role'}).update(id);			
			userSelect.insert(option);
			rolesId.push(id);
		});
		availableRoles.each(function(xmlElement){
			var id = xmlElement.getAttribute('id');
			if(!rolesId.include(id)){
				var option = new Element('div', {id:id, className:'ajxp_role available_role'}).update(id);
				availSelect.insert(option);
			}
		});
		this.draggables = $A();
		rolesPane.select("div.ajxp_role").each(function(item){
			var container = item.parentNode;
			this.draggables.push(new Draggable(item, {
				revert:true, 
				ghosting:false, 
				onStart:function(){
					container.parentNode.insert(item);
				},
				onEnd : function(){
					if(item.ajxp_dropped) return;
					container.insert(item);
				},
				reverteffect:function(element){element.setStyle({top:0,left:0});}
			}));
		}.bind(this));
		var dropFunc = function(dragged, dropped, event){
			dragged.ajxp_dropped = true;
			dropped.insert(dragged);
			dragged.setStyle({top:0,left:0});
			var action = "edit";
			var sub_action;
			if(dragged.hasClassName('user_role')){
				dragged.removeClassName('user_role');
				dragged.addClassName('available_role');
				sub_action = "user_delete_role";
			}else{
				dragged.removeClassName('available_role');
				dragged.addClassName('user_role');
				sub_action = "user_add_role";
			}
			if(this.userId){
				var conn = new Connexion();
				conn.setParameters($H({get_action:"edit", sub_action:sub_action, user_id:this.userId, role_id:dragged.id}));
				conn.onComplete = function(transport){
					this.parseXmlMessage(transport.responseXML);
					this.loadUser(this.userId, true);
					ajaxplorer.fireContextRefresh();
				}.bind(this);
				conn.sendAsync();			
			}else if(this.selectionUrl){
				var connexion = new Connexion(this.selectionUrl);
				connexion.addParameter("update_role_action", (sub_action=="user_delete_role"?"remove":"add"));
				connexion.addParameter("role_id", dragged.id);
				connexion.onComplete = function(transport){			
					this.clearRolesForm();
					this.populateRoles(transport.responseXML);
					ajaxplorer.fireContextRefresh();
				}.bind(this);
				connexion.sendAsync();				
			}
		}.bind(this);
		Droppables.add(availSelect, {accept:'user_role', onDrop:dropFunc, hoverclass:'roles_hover'});
		Droppables.add(userSelect, {accept:'available_role', onDrop:dropFunc, hoverclass:'roles_hover'});
		modal.setCloseAction(function(){
			Droppables.remove(availSelect);
			Droppables.remove(userSelect);
			this.draggables.invoke('destroy');
		}.bind(this));
		
	},
	
	generateRightsTable : function(xmlData){
		var rightsPane = this.form.select('[id="rights_pane"]')[0];
		var rightsTable = rightsPane.select('tbody')[0];		
		var repositories = $A(XPathSelectNodes(xmlData, "//repo"));
        repositories.sortBy(function(element) {return XPathGetSingleNodeText(element, "label");});
        var odd = true;
		for(var i=0;i<repositories.length;i++){
			var repoNode = repositories[i];
			var repoLabel = XPathGetSingleNodeText(repoNode, "label");
			var repoId = XPathGetSingleNodeText(repoNode, "@id");
			var accessType = XPathGetSingleNodeText(repoNode, "@access_type");
			//if(accessType == "ajxp_shared") continue;
			
			var readBox = new Element('input', {type:'checkbox', id:'chck_'+repoId+'_read'}).setStyle({width:'25px'});
			var writeBox = new Element('input', {type:'checkbox', id:'chck_'+repoId+'_write'}).setStyle({width:'25px'});
			
			readBox.observe('click', this.changeUserOrRoleRight.bind(this));
			writeBox.observe('click', this.changeUserOrRoleRight.bind(this));
			
			var rightsCell = new Element('td', {width:'55%', align:'right'});
			rightsCell.insert(MessageHash['ajxp_conf.29'] + ' ');
			rightsCell.insert(readBox);
			rightsCell.insert(MessageHash['ajxp_conf.30'] + ' ');
			rightsCell.insert(writeBox);
			var tr = new Element('tr', {className:(odd?'odd':'even')});
			odd = !odd;
			var titleCell = new Element('td', {width:'45%'}).update('<img src="'+ajxpResourcesFolder+'/images/mimes/16/folder_red.png" style="float:left;margin-right:5px;">'+repoLabel);
			tr.insert(titleCell);
			tr.insert(rightsCell);
			rightsTable.insert({bottom:tr});			

			// FOR IE, set checkboxes state AFTER dom insertion.
			readBox.checked = (XPathGetSingleNodeText(repoNode, "@r")=='1');
			writeBox.checked = (XPathGetSingleNodeText(repoNode, "@w")=='1');			
		}
			
		rightsTable.select('[id="loading_row"]')[0].remove();				
	},
	
	generateActionRightsPane : function(xmlData, clear){
		var actionPane = this.form.down('#actions_pane');
		var textfield = actionPane.down('#disabled_actions');
		if(!clear){
			var submitButton = actionPane.down('#submit_actions_pane');
			submitButton.observe("click", function(e){
				Event.stop(e);
				var conn = new Connexion();
				conn.addParameter("get_action", "edit");
				conn.addParameter("sub_action", "update_role_actions");
				conn.addParameter("role_id", this.roleId);
				conn.addParameter("disabled_actions", textfield.getValue());
				conn.onComplete = function(transport){
					this.generateActionRightsPane(transport.responseXML, true);
				}.bind(this);
				conn.sendAsync();
			}.bind(this) );
		}
		var actionNodes = XPathSelectNodes(xmlData, 'admin_data/actions_rights/action[@value="false"]');
		var disabled = [];
		for(var i=0;i<actionNodes.length;i++){
			disabled.push(actionNodes[i].getAttribute('name'));
		}
		textfield.value = disabled.join(',');
		if(clear){
			ajaxplorer.displayMessage('SUCCESS', MessageHash['ajxp_conf.87']);
		}
	},
	
	loadCreateUserForm : function(){
		var params = new Hash();
		params.set("get_action", "edit");
		params.set("sub_action", "get_custom_params");
		var connexion = new Connexion();
		connexion.setParameters(params);
		connexion.onComplete = function(transport){
			this.generateCustomPane(transport.responseXML, true);			
			modal.refreshDialogPosition();
			modal.refreshDialogAppearance();
			ajaxplorer.blurAll();
		}.bind(this);
		connexion.sendAsync();
	},
		
	generateCustomPane : function(xmlData, nosubmit){
		var customPane = this.form.select("#custom_pane")[0];
		var customData = XPathSelectNodes(xmlData, "admin_data/custom_data/param");							
		if(!customData.length) {
			return;
		}
		customPane.show();		
		customPane.previous().show();
		var iswallet = (nosubmit) ? '' : "wallet_pane"
		var customDiv = new Element('div', {className:iswallet, id:"custom_pane"});
		customPane.insert(customDiv);
		
		var customParams = $A([]);
		for(var i=0;i<customData.length;i++){
			customParams.push(this.driverParamNodeToHash(customData[i]));
		}
		var userId = (this.userId) ? this.userId : 'new';
		var newTd = new Element('div', {className:'driver_form', id:'custom_params_'+userId});
		customDiv.insert(newTd);
		var customValues = $H({});
		for(i=0;i<customData.length;i++){
			var tag = customData[i];
			customValues.set(tag.getAttribute('name'), tag.getAttribute('value'));
		}
		this.createParametersInputs(newTd, customParams, false, customValues);
		if(!nosubmit){
			var submitButton = new Element('input', {type:'image', value:'SAVE', className:'dialogButton', onClick:'return false;', src:resolveImageSource("dialog_ok_apply.png", "/images/actions/22")});
			submitButton.observe("click", function(){
				this.submitUserCustomForm(userId);
			}.bind(this));
			newTd.insert({before: submitButton});
		}
		// recompute heights
		var newHeight  = 28*(customParams.length);
		newTd.setStyle({height:newHeight});
		if(!nosubmit)
			submitButton.setStyle({marginTop:(parseInt(newHeight/2)-10)});
	},
	
	generateWalletsPane : function(xmlData){
		var wallets = this.form.select("#wallets_pane")[0];
		var repositories = $A(XPathSelectNodes(xmlData, "//repo"));
        repositories.sortBy(function(element) {return XPathGetSingleNodeText(element, "label");});
		for(var i=0;i<repositories.length;i++){
			var repoNode = repositories[i];
			var repoLabel = XPathGetSingleNodeText(repoNode, "label");
			var repoId = XPathGetSingleNodeText(repoNode, "@id");
			var accessType = XPathGetSingleNodeText(repoNode, "@access_type");

			var walletParams = XPathSelectNodes(xmlData, "admin_data/drivers/ajxpdriver[@name='"+accessType+"']/user_param");				
			var walletValues = XPathSelectNodes(xmlData, "admin_data/user_wallet/wallet_data[@repo_id='"+repoId+"']");			
			if(!walletParams.length) continue;			
			
			var walletPane = new Element('div', {className:"wallet_pane", id:"wallet_pane_"+repoId});
			this.addRepositoryUserParams(walletPane, repoId, walletParams, walletValues);
			wallets.insert(new Element('div', {style:'margin-top: 10px;'}).update(MessageHash['ajxp_conf.79']+' "<b>'+ repoLabel + '</b>"'));
			wallets.insert(walletPane);
		}			
	},
	
	addRepositoryUserParams : function(walletPane, repoId, walletParams, walletValues){
		var repoParams = $A([]);
		for(var i=0;i<walletParams.length;i++){
			repoParams.push(this.driverParamNodeToHash(walletParams[i]));
		}
		
		var userId = this.userId;		
		var newTd = new Element('div', {className:'driver_form', id:'repo_user_params_'+userId+'_'+repoId});
		walletPane.insert(newTd);
		var repoValues = $H({});
		for(i=0;i<walletValues.length;i++){
			var tag = walletValues[i];
			repoValues.set(tag.getAttribute('option_name'), tag.getAttribute('option_value'));
		}
		this.createParametersInputs(newTd, repoParams, false, repoValues);
		var submitButton = new Element('input', {type:'image', value:'SAVE', className:'dialogButton', onClick:'return false;', src:resolveImageSource("dialog_ok_apply.png", "/images/actions/22")});
		submitButton.observe("click", function(){
			this.submitUserParamsForm(userId, repoId);
		}.bind(this));
		newTd.insert({before: submitButton});
		// recompute heights
		var newHeight  = 28*(walletParams.length);
		newTd.setStyle({height:newHeight});
		submitButton.setStyle({marginTop:(parseInt(newHeight/2)-10)});		
	},
	
	submitUserCustomForm : function(userId){
		var parameters = new Hash();
		parameters.set('user_id', userId);
		if(this.submitParametersInputs($('custom_params_'+userId), parameters, "DRIVER_OPTION_")){
			this.displayMessage("ERROR", MessageHash['ajxp_conf.36']);
			return false;
		}
		this.submitForm("edit_user", 'save_custom_user_params', parameters, null);
	},
	
	submitUserParamsForm : function(userId, repositoryId){
		var parameters = new Hash();
		parameters.set('user_id', userId);
		parameters.set('repository_id', repositoryId);
		if(this.submitParametersInputs($('repo_user_params_'+userId+'_'+repositoryId), parameters, "DRIVER_OPTION_")){
			this.displayMessage("ERROR", MessageHash['ajxp_conf.36']);
			return false;
		}
		this.submitForm("edit_user", 'save_repository_user_params', parameters, null);
	},
	
		
	changeUserOrRoleRight: function(event){	
		var oChckBox = Event.element(event);
		var parts = oChckBox.id.split('_');		
		// Remove "chck" prefix (first part)
		parts.shift();
		// Get and remove right name (last part)
		var rightName = parts.pop();
		// Rebuild repository id (can contain underscore!)
		var repositoryId = parts.join("_");
		
		var newState = oChckBox.checked;
		oChckBox.checked = !oChckBox.checked;
		oChckBox.disabled = true;		
		var rightString;
		
		var emptyRight = '';
		if(this.userId){
			emptyRight = 'n';
		}
		
		if(rightName == 'read') 
		{
			$('chck_'+repositoryId+'_write').disabled = true;
			var wState = $('chck_'+repositoryId+'_write').checked;
			rightString = (newState?(wState?'rw':'r'):(wState?'w':'n'));
		}
		else 
		{
			$('chck_'+repositoryId+'_read').disabled = true;
			rightString = (newState?'rw':($('chck_'+repositoryId+'_read').checked?'r':'n'));
		}
				
		var parameters = new Hash();
		var sub_action;
		if(this.userId){
			parameters.set('user_id', this.userId);
			sub_action = 'update_user_right';
		}else{
			parameters.set('role_id', this.roleId);
			sub_action = 'update_role_right';
		}
		parameters.set('repository_id', repositoryId);
		parameters.set('right', rightString);
		this.submitForm("edit_user", sub_action, parameters, null);
	},
	
	changeAdminRight: function(oChckBox){
		var boxValue = oChckBox.checked;
		var parameters = new Hash();
		parameters.set('user_id', this.userId);
		parameters.set('right_value', (boxValue?'1':'0'));
		this.submitForm("edit_user", 'change_admin_right', parameters, null);
	},
	
	encodePassword : function(password){
		// First get a seed to check whether the pass should be encoded or not.
		var sync = new Connexion();
		var seed;
		sync.addParameter('get_action', 'get_seed');
		sync.onComplete = function(transport){
			seed = transport.responseText;			
		}		
		sync.sendSync();
		var encoded;
		if(seed != '-1'){
			encoded = hex_md5(password);
		}else{
			encoded = password;
		}
		return encoded;
		
	},
	
	changePassword: function(){
		var newPass = $('new_pass');
		var newPassConf = $('new_pass_confirm');
		if(newPass.value == '' || newPass.value.length < window.ajxpBootstrap.parameters.get("password_min_length")){
			this.displayMessage('ERROR', MessageHash[378]);
			return;
		}
		if(newPass.value != newPassConf.value){
			 this.displayMessage('ERROR', MessageHash['ajxp_conf.37']);
			 return;
		}
		// First get a seed to check whether the pass should be encoded or not.
		parameters = new Hash();
		parameters.set('user_id', this.userId);
		parameters.set('user_pwd', this.encodePassword(newPass.value));
		this.submitForm("edit_user", 'update_user_pwd', parameters, null);
		newPass.value = '';
		newPassConf.value = '';
	},
	
	submitCreateUser : function(){
		var login = this.form.select('[name="new_user_login"]')[0];
		var pass = this.form.select('[name="new_user_pwd"]')[0];
		var passConf = this.form.select('[name="new_user_pwd_conf"]')[0];
		var extraParams = this.form.select('div#custom_pane input');
		
		if(login.value == ''){
			ajaxplorer.displayMessage("ERROR", MessageHash['ajxp_conf.38']);
			return false;
		}
		if(pass.value == '' || passConf.value == '' ){
			ajaxplorer.displayMessage("ERROR", MessageHash['ajxp_conf.39']);
			return false;
		}
		if(pass.value.length < window.ajxpBootstrap.parameters.get("password_min_length")){
			ajaxplorer.displayMessage("ERROR", MessageHash[378]);
			return false;
		}
		if(pass.value != passConf.value){
			ajaxplorer.displayMessage("ERROR", MessageHash['ajxp_conf.37']);
			return false;
		}
		parameters = new Hash();
		parameters.set('new_user_login', login.value);
		parameters.set('new_user_pwd', this.encodePassword(pass.value));
		extraParams.each( function(input){
			parameters.set('DRIVER_OPTION_'+input.name, input.value);
		});
		this.submitForm("create_user", 'create_user', parameters, null);
		return true;		
	},

	deleteUser: function(){
		var chck = this.form.select('[id="delete_confirm"]')[0];
		if(!chck.checked){
			this.displayMessage("ERROR", MessageHash['ajxp_conf.40']);
			return;
		}
		parameters = new Hash();
		parameters.set('user_id', this.userId);
		this.submitForm("edit_user", 'delete_user', parameters, null);
		chck.checked = false;
	},
		
	
	createUser: function (){
		var login = $('new_user_login');
		var pass = $('new_user_pwd');
		var passConf = $('new_user_pwd_conf');
		if(login.value == ''){
			this.displayMessage("ERROR", MessageHash['ajxp_conf.38']);
			return;
		}
		if(pass.value == '' || passConf.value == ''){
			this.displayMessage("ERROR", MessageHash['ajxp_conf.39']);
			return;
		}
		if(pass.value != passConf.value){
			this.displayMessage("ERROR", MessageHash['ajxp_conf.37']);
			return;
		}
		
		var parameters = new Hash();
		parameters.set('new_login', login.value);
		parameters.set('new_pwd', pass.value);
		this.submitForm("edit_user", 'create_user', parameters, null);
		login.value = pass.value = passConf.value = '';
		return;
		
	},
		
	/*************************************/
	/* 		ROLES FUNCTIONS				 */
	/*************************************/
	loadRole : function(roleId){
		this.userId = null;
		this.roleId = roleId;
		this.form.down('fieldset').insert({top:new Element('legend').update(MessageHash["ajxp_conf.77"])});
		this.form.down('#roles_pane').remove();
		this.form.down('#rights_legend').remove();		
		this.form.down('#actions_pane').show();
		var params = new Hash();
		params.set("get_action", "edit");
		params.set("sub_action", "edit_role");
		params.set("role_id", roleId);
		var connexion = new Connexion();
		connexion.setParameters(params);
		connexion.onComplete = function(transport){
			this.generateRightsTable(transport.responseXML);
			this.generateActionRightsPane(transport.responseXML);
			modal.refreshDialogPosition();
			modal.refreshDialogAppearance();
			ajaxplorer.blurAll();
		}.bind(this);
		connexion.sendAsync();				
	},
	

	/*************************************/
	/*       REPOSITORIES FUNCTIONS      */
	/*************************************/
	initCreateRepoWizard : function(){
		this.newRepoLabelInput = this.form.select('input[type="text"]')[0];
		this.driverSelector = this.form.select('select')[0];
		this.driverForm = this.form.select('div[id="driver_form"]')[0];
		this.repoSubmitButton = this.form.select('input[id="submit_create_repo"]')[0];
		this.repoSubmitButton.observe("click", function(){
			this.repoButtonClick(true);
		}.bind(this));
		this.drivers = new Hash();
		this.submitForm('create_repository', 'get_drivers_definition', new Hash(), null, function(xmlData){			
			var driverNodes = XPathSelectNodes(xmlData, "drivers/ajxpdriver");			
			for(var i=0;i<driverNodes.length;i++){				
				var driver = driverNodes[i];
				var driverDef = new Hash();
				var driverLabel = XPathGetSingleNodeText(driver, "@label");
				var driverName = XPathGetSingleNodeText(driver, "@name");
				var driverParams = XPathSelectNodes(driver, "param");
				driverDef.set('label', driverLabel);
				driverDef.set('description', XPathGetSingleNodeText(driver, "@description"));
				driverDef.set('name', driverName);
				var driverParamsArray = new Array();
				for(j=0;j<driverParams.length;j++){
					var paramNode = driverParams[j];
					driverParamsArray.push(this.driverParamNodeToHash(paramNode));
				}
				driverDef.set('params', driverParamsArray);
				this.drivers.set(driverName, driverDef);
			}
			this.updateDriverSelector();
		}.bind(this) );
	},
	
	updateDriverSelector : function(){				
		if(!this.drivers || !this.driverSelector) return;
		if(Prototype.Browser.IE){this.driverSelector.hide();}
		this.driverSelector.update('<option value="0"></option>');
		this.drivers.each(function(pair){
			var option = new Element('option');
			option.setAttribute('value', pair.key);
			option.update(pair.value.get('label'));
			this.driverSelector.insert({'bottom':option});			
		}.bind(this) );
		if(Prototype.Browser.IE){this.driverSelector.show();}
		this.driverSelector.onchange = this.driverSelectorChange.bind(this);
	},
	
	driverSelectorChange : function(){
		var height = (Prototype.Browser.IE?62:32);
		var dName = this.driverSelector.getValue();
		this.createDriverForm(dName);
		if(dName != "0"){
			var height = 130 + this.driverForm.getHeight() + (Prototype.Browser.IE?15:0);
			if(height > 425) height=425;
		}
		new Effect.Morph(this.driverForm.up('div'),{
			style:'height:'+height + 'px',
			duration:0.3, 
			afterFinish : function(){
				modal.refreshDialogPosition();
				modal.refreshDialogAppearance();			
			}
		});		
	},
	
	createDriverForm : function(driverName){
		if(driverName == "0"){
			this.driverForm.update('');
			return;
		}
		var dOpt = this.drivers.get(driverName);
		this.driverForm.update('<div style="padding-top:2px; padding-left:127px; margin-top: 30px; color:#79f; border-top:1px solid #ccc;">' + dOpt.get('description')+'<br></div>');
		this.createParametersInputs(this.driverForm, dOpt.get('params'), false);
	},
	
	repoButtonClick  : function(validate){
		if(!validate) {
			this.newRepoLabelInput.value = '';
			this.driverSelector.selectedIndex = 0;
			this.driverSelectorChange();
			return false;		
		}
		var toSubmit = new Hash();
		var missingMandatory = false;
		if(this.newRepoLabelInput.value == ''){
			missingMandatory = true;
		}else{
			toSubmit.set('DISPLAY', this.newRepoLabelInput.value);
		}
		toSubmit.set('DRIVER', this.driverSelector.options[this.driverSelector.selectedIndex].value);
		
		if(missingMandatory || this.submitParametersInputs(this.driverForm, toSubmit, 'DRIVER_OPTION_')){
			this.displayMessage("ERROR", MessageHash['ajxp_conf.36']);
			return false;
		}		
		this.submitForm('edit_repository', 'create_repository', toSubmit, null, function(){
			hideLightBox();			
		}.bind(this));
		return false;		
	},
	
	loadRepository : function(repId, metaTab){
		var params = new Hash();		
		params.set("get_action", "edit");
		params.set("sub_action", "edit_repository");
		params.set("repository_id", repId);
		var connexion = new Connexion();
		connexion.setParameters(params);
		connexion.onComplete = function(transport){
			this.feedRepositoryForm(transport.responseXML, metaTab);			
			modal.refreshDialogPosition();
			modal.refreshDialogAppearance();
			ajaxplorer.blurAll();
		}.bind(this);
		connexion.sendAsync();		
	},

	feedRepositoryForm: function(xmlData, metaTab){
		
		var repo = XPathSelectSingleNode(xmlData, "admin_data/repository");
		var driverParams = XPathSelectNodes(xmlData, "admin_data/ajxpdriver/param");
		var optionsPane = this.form.select('[id="options_pane"]')[0];		
			
		var driverParamsHash = $A([]);
		for(var i=0;i<driverParams.length;i++){
			driverParamsHash.push(this.driverParamNodeToHash(driverParams[i]));
		}
				
		var form = new Element('div', {className:'driver_form'});
		var metaForm = new Element('div', {className:'driver_form', style:'display:none;'});
		
		var link1 = XPathGetSingleNodeText(xmlData, "admin_data/ajxpdriver/@name").toUpperCase()+' '+ MessageHash['ajxp_conf.41'];
		var link2 = MessageHash['ajxp_conf.10'];
		
		var legend = this.createTabbedFieldset(link1, form, link2, metaForm);
		optionsPane.update(legend);
		optionsPane.insert({bottom:form});
		optionsPane.insert({bottom:metaForm});
		
		/*
		var optLegend = new Element('a', {className:"active"}).update(XPathGetSingleNodeText(xmlData, "admin_data/ajxpdriver/@name").toUpperCase()+' '+ MessageHash['ajxp_conf.41']);
		var metaLegend = new Element('a').update(MessageHash['ajxp_conf.10']);
		var legend = new Element('legend');
		legend.insert(optLegend);
		legend.insert(" | ");
		legend.insert(metaLegend);
				
		optLegend.observe("click", function(){
			metaForm.hide();form.show();
			optLegend.addClassName('active');
			metaLegend.removeClassName('active');
			modal.refreshDialogAppearance();
		});
		metaLegend.observe("click", function(){
			form.hide();metaForm.show();
			metaLegend.addClassName('active');
			optLegend.removeClassName('active');
			modal.refreshDialogAppearance();
		});
		*/
				
		var paramsValues = new Hash();
		$A(repo.childNodes).each(function(child){
			if(child.nodeName != 'param') return;
			paramsValues.set(child.getAttribute('name'), child.getAttribute('value'));
		});
		var writeable = (repo.getAttribute("writeable")?(repo.getAttribute("writeable")=="true"):false);			
		this.currentForm = form;
		this.currentRepoId = repo.getAttribute("index");
		this.currentRepoWriteable = writeable;
		this.createParametersInputs(form, driverParamsHash, false, paramsValues, !writeable);
		
		if(writeable){
			this.feedMetaSourceForm(xmlData, metaForm);		
			if(metaTab){
				form.hide();metaForm.show();
				metaLegend.addClassName('active');
				optLegend.removeClassName('active');
				modal.refreshDialogAppearance();			
			}
		}else{
			metaForm.update(MessageHash['ajxp_conf.88']);
		}
		
	},
	
	feedMetaSourceForm : function(xmlData, metaPane){
		var data = XPathSelectSingleNode(xmlData, 'admin_data/repository/param[@name="META_SOURCES"]');
		if(data && data.firstChild && data.firstChild.nodeValue){
			metaSourcesData = data.firstChild.nodeValue.evalJSON();
			for(var plugId in metaSourcesData){
				var form = new Element("div", {className:"metaPane"}).update("<img name=\"delete_meta_source\" src=\""+ajxpResourcesFolder+"/images/actions/16/editdelete.png\"><img name=\"edit_meta_source\" src=\""+ajxpResourcesFolder+"/images/actions/16/filesave.png\"><span class=\"title\">Plugin '"+plugId+"'</span>");
				form._plugId = plugId;
				var metaDefNodes = XPathSelectNodes(xmlData, 'admin_data/metasources/meta[@id="'+plugId+'"]/param');
				var driverParamsHash = $A([]);
				for(var i=0;i<metaDefNodes.length;i++){
					driverParamsHash.push(this.driverParamNodeToHash(metaDefNodes[i]));
				}
				paramsValues = new Hash(metaSourcesData[plugId]);
				this.createParametersInputs(form, driverParamsHash, true, paramsValues, false);
				metaPane.insert(form);
			}
		}
		var addForm = new Element("div", {className:"metaPane"}).update("<div style='clear:both;'><img name=\"add_meta_source\" src=\""+ajxpResourcesFolder+"/images/actions/16/filesave.png\"><span class=\"title\">"+MessageHash['ajxp_conf.11']+"</span></div>");
		var formEl = new Element("div", {className:"SF_element"}).update("<div class='SF_label'>"+MessageHash['ajxp_conf.12']+" :</div>");
		this.metaSelector = new Element("select", {name:'new_meta_source', className:'SF_input'});
		var choices = XPathSelectNodes(xmlData, 'admin_data/metasources/meta');
		this.metaSelector.insert(new Element("option", {value:"", selected:"true"}));
		for(var i=0;i<choices.length;i++){
			var id = choices[i].getAttribute("id");
			this.metaSelector.insert(new Element("option",{value:id}).update(id));
		}		
		addForm.insert(formEl);
		formEl.insert(this.metaSelector);
		metaPane.insert(addForm);
		var addFormDetail = new Element("div");
		addForm.insert(addFormDetail);
		addForm.select('img')[0]._form = addForm;
		
		this.metaSelector.observe("change", function(){
			var plugId = this.metaSelector.getValue();
			addFormDetail.update("");
			if(plugId){
				var metaDefNodes = XPathSelectNodes(xmlData, 'admin_data/metasources/meta[@id="'+plugId+'"]/param');
				var driverParamsHash = $A([]);
				for(var i=0;i<metaDefNodes.length;i++){
					driverParamsHash.push(this.driverParamNodeToHash(metaDefNodes[i]));
				}				
				this.createParametersInputs(addFormDetail, driverParamsHash, true);				
			}
			modal.refreshDialogAppearance();
		}.bind(this));

		metaPane.select('img').each(function(img){
			img.observe("click", this.metaActionClick.bind(this));
		}.bind(this));
		
	},
	
	metaActionClick : function(event){
		var img = Event.findElement(event, 'img');
		if(img._form){
			var form = img._form;
		}else{
			var form = Event.findElement(event, 'div');
		}
		//var params = target._parameters;
		var params = new Hash();
		if(form._plugId){
			params.set('plugId', form._plugId);
		}
		if(img.getAttribute('name')){
			params.set('get_action', img.getAttribute('name'));
		}
		params.set('repository_id', this.currentRepoId);
		this.submitParametersInputs(form, params, "DRIVER_OPTION_");
		if(params.get('get_action') == 'add_meta_source' && params.get('DRIVER_OPTION_new_meta_source') == ''){
			alert(MessageHash['ajxp_conf.42']);
			return;
		}
		if(params.get('DRIVER_OPTION_new_meta_source')){
			params.set('new_meta_source', params.get('DRIVER_OPTION_new_meta_source'));
			params.unset('DRIVER_OPTION_new_meta_source');
		}
		if(params.get('get_action') == 'delete_meta_source'){
			var res = confirm(MessageHash['ajxp_conf.13']);
			if(!res) return;
		}
		
		var conn = new Connexion();
		conn.setParameters(params);
		conn.onComplete = function(transport){
			this.parseXmlMessage(transport.responseXML);
			this.loadRepository(this.currentRepoId, true);
		}.bind(this);
		conn.sendAsync();
		
	},
		
	deleteRepository : function(repId){
		var params = new Hash();
		params.set('repository_id', repId);
		this.submitForm('edit_repository', 'delete_repository', params, null, function(){
			
		}.bind(this));
	},
		
	/*************************************/
	/*       COMMON FUNCTIONS            */
	/*************************************/	
	driverParamNodeToHash : function(driverNode){
		var driversAtts = $A(['name', 'type', 'label', 'description', 'default', 'mandatory']);
		var driverHash = new Hash();
		driversAtts.each(function(attName){
			driverHash.set(attName, (XPathGetSingleNodeText(driverNode, '@'+attName) || ''));
		});
		return driverHash;
	},	
	
	createTabbedFieldset: function(link1, pane1, link2, pane2){
		var legend1 = new Element('a', {className:"active"}).update(link1);
		var legend2 = new Element('a').update(link2);
		var legend = new Element('legend');
		legend.insert(legend1);
		legend.insert(" | ");
		legend.insert(legend2);		
		
		legend1.observe("click", function(){
			pane2.hide();pane1.show();
			legend1.addClassName('active');
			legend2.removeClassName('active');
			modal.refreshDialogAppearance();
		});
		legend2.observe("click", function(){
			pane1.hide();pane2.show();
			legend2.addClassName('active');
			legend1.removeClassName('active');
			modal.refreshDialogAppearance();
		});				
		return legend;
	},
	
	createParametersInputs : function(form, parametersDefinitions, showTip, values, disabled){
		parametersDefinitions.each(function(param){		
			var label = param.get('label');
			if(param.get('labelId')){
				label = MessageHash[param.get('labelId')];
			}
			var name = param.get('name');
			var type = param.get('type');
			var desc = param.get('description');
			if(param.get('descriptionId')){
				desc = MessageHash[param.get('descriptionId')];
			}
			var mandatory = false;
			if(param.get('mandatory') && param.get('mandatory')=='true') mandatory = true;
			var defaultValue = (values?'':(param.get('default') || ""));
			if(values && values.get(name)){
				defaultValue = values.get(name);
			}
			var element;
			var disabledString = (disabled?' disabled="true" ':'');
			if(type == 'string' || type == 'integer'){
				element = '<input type="text" ajxp_type="'+type+'" ajxp_mandatory="'+(mandatory?'true':'false')+'" name="'+name+'" value="'+defaultValue+'"'+disabledString+' class="SF_input">';
		    }else if(type == 'password'){
				element = '<input type="password" autocomplete="off" ajxp_type="'+type+'" ajxp_mandatory="'+(mandatory?'true':'false')+'" name="'+name+'" value="'+defaultValue+'"'+disabledString+' class="SF_input">';
			}else if(type == 'boolean'){
				var selectTrue, selectFalse;
				if(defaultValue){
					if(defaultValue == "true" || defaultValue == "1") selectTrue = true;
					if(defaultValue == "false" || defaultValue == "0") selectFalse = true;
				}
				element = '<input type="radio" ajxp_type="'+type+'" class="SF_box" name="'+name+'" value="true" '+(selectTrue?'checked':'')+''+disabledString+'> Yes';
				element = element + '<input type="radio" ajxp_type="'+type+'" class="SF_box" name="'+name+'" '+(selectFalse?'checked':'')+' value="false"'+disabledString+'> No';
				element = '<div class="SF_input">'+element+'</div>';
			}
			var div = new Element('div', {className:"SF_element"}).update('<div class="SF_label">'+label+(mandatory?'*':'')+' :</div>'+element);
			form.insert({'bottom':div});
			if(desc){
				modal.simpleTooltip(div.select('.SF_label')[0], desc);
			}
		});
	},
	
	submitParametersInputs : function(form, parametersHash, prefix){
		prefix = prefix || '';
		var missingMandatory = false;
		form.select('input').each(function(el){			
			if(el.type == "text" || el.type == "password"){
				if(el.getAttribute('ajxp_mandatory') == 'true' && el.value == ''){
					missingMandatory = true;
				}
				parametersHash.set(prefix+el.name, el.value);				
			}
			else if(el.type=="radio" && el.checked){
				parametersHash.set(prefix+el.name, el.value)
			};
			if(el.getAttribute('ajxp_type')){
				parametersHash.set(prefix+el.name+'_ajxptype', el.getAttribute('ajxp_type'));
			}
		});		
		form.select('select').each(function(el){
			if(el.getAttribute("ajxp_mandatory") == 'true' && el.getValue() == ''){
				missingMandatory = true;
			}
			parametersHash.set(prefix+el.name, el.getValue());
		});
		return missingMandatory;
	},	
	
	submitForm: function(mainAction, action, parameters, formName, callback){
		//var connexion = new Connexion('admin.php');
		var connexion = new Connexion();
		if(formName)
		{
			$(formName).getElements().each(function(fElement){
				connexion.addParameter(fElement.name, fElement.getValue());
			});	
		}
		if(parameters)
		{
			parameters.set('get_action', "edit");			
			parameters.set('sub_action', action);
			connexion.setParameters(parameters);
		}
		if(!callback){
			connexion.onComplete = function(transport){this.parseXmlMessage(transport.responseXML);}.bind(this);
		}else{
			connexion.onComplete = function(transport){
				this.parseXmlMessage(transport.responseXML);
				callback(transport.responseXML);
			}.bind(this);
		}
		connexion.sendAsync();
	},
	
	loadHtmlToDiv: function(div, parameters, completeFunc){
		var connexion = new Connexion();
		parameters.each(function(pair){
			connexion.addParameter(pair.key, pair.value);
		});
		connexion.onComplete = function(transport){		
			$(div).update(transport.responseText);
			if(completeFunc) completeFunc();
		};
		connexion.sendAsync();	
	},
	
	
	parseXmlMessage: function(xmlResponse){
		if(xmlResponse == null || xmlResponse.documentElement == null) return;
		var childs = xmlResponse.documentElement.childNodes;	
		var driversList = false;
		var driversAtts = $A(['name', 'type', 'label', 'description', 'default', 'mandatory']);
		var repList = false;
		var logFilesList = false;
		var logsList = false;
		
		for(var i=0; i<childs.length;i++)
		{
			if(childs[i].nodeName == "message")
			{
				this.displayMessage(childs[i].getAttribute('type'), childs[i].firstChild.nodeValue);
			}
			else if(childs[i].nodeName == "update_checkboxes")
			{
				var userId = childs[i].getAttribute('user_id');
				var repositoryId = childs[i].getAttribute('repository_id');
				var read = childs[i].getAttribute('read');
				var write = childs[i].getAttribute('write');
				if(read != 'old') $('chck_'+repositoryId+'_read').checked = (read=='1'?true:false);
				$('chck_'+repositoryId+'_read').disabled = false;
				if(write != 'old') $('chck_'+repositoryId+'_write').checked = (write=='1'?true:false);
				$('chck_'+repositoryId+'_write').disabled = false;
			}
			else if(childs[i].nodeName == "repository")
			{
				if(!this.repositories || !repList) this.repositories = new Hash();
				repList = true;
				this.repositories.set(childs[i].getAttribute('index'), childs[i]);
			}
			else if(childs[i].tagName == "reload_instruction")
			{
				var obName = childs[i].getAttribute('object');
				if(obName == 'list')
				{
					var file = childs[i].getAttribute('file');
					ajaxplorer.getContextHolder().setPendingSelection(file);
					ajaxplorer.fireContextRefresh();
					ajaxplorer.getContextHolder().clearPendingSelection();
				}else if(obName == "repository_list"){
					ajaxplorer.reloadRepositoriesList();
				}
			}			
		}
	},

	closeMessageDiv: function(){
		if(this.messageDivOpen)
		{
			new Effect.Fade(this.messageBox);
			this.messageDivOpen = false;
		}
	},
	
	tempoMessageDivClosing: function(){
		this.messageDivOpen = true;
		setTimeout(function(){this.closeMessageDiv();}.bind(this), 3000);
	},
	
	displayMessage: function(messageType, message){
		this.messageBox = $('message_div');
		if(!this.messageBox){
			this.messageBox = new Element("div", {title:MessageHash[98],id:"message_div",className:"messageBox"});
			$(document.body).insert(this.messageBox);
			this.messageContent = new Element("div", {id:"message_content"});
			this.messageBox.update(this.messageContent);
			this.messageBox.observe("click", this.closeMessageDiv.bind(this));
		}		
		message = message.replace(new RegExp("(\\n)", "g"), "<br>");
		if(messageType == "ERROR"){ this.messageBox.removeClassName('logMessage');  this.messageBox.addClassName('errorMessage');}
		else { this.messageBox.removeClassName('errorMessage');  this.messageBox.addClassName('logMessage');}
		$('message_content').innerHTML = message;
		this.messageBox.style.top = '80%';
		this.messageBox.style.left = '60%';
		this.messageBox.style.width = '30%';
		new Effect.Corner(this.messageBox,"round");
		new Effect.Appear(this.messageBox);
		this.tempoMessageDivClosing();
	}
});
