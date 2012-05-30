function getAjxpMobileActions(){
	var mobileActions = $('mobile_actions_copy');
	if(mobileActions){
		return mobileActions;
	}
	mobileActions = $('mobile_actions').clone(true);
	mobileActions.id = "mobile_actions_copy";
	var act = mobileActions.select('a');
	act[0].observe('click', function(e){
		Event.stop(e);
		$('info_container').down('.info_panel_title_span').update(ajaxplorer.getContextHolder().getUniqueNode().getLabel());
		$('info_container').show();
		$('info_container').ajxpPaneObject.resize();
		$('info_panel').select('.infoPanelActions a').each(function(action){
			action.observe("click", function(){$('info_container').hide();});
		});
	});
	act[1].observe('click', function(e){		
		ajaxplorer.actionBar.fireAction(act[1]._action);
		Event.stop(e);
	});
	return mobileActions;
}

function initAjxpMobileActions(){
	document.observe("ajaxplorer:selection_changed", function(e){
		var list = e.memo._selectionSource;
		if(!list) return;
		var mobileActions = getAjxpMobileActions();
		mobileActions.hide();
		var items = list.getSelectedItems();
		var node = ajaxplorer.getContextHolder().getUniqueNode();
		var a = mobileActions.select('a')[1];		
		
		if(node && node.isLeaf()){
			//mobileActions.select('a')[1].hide();
			var editors = ajaxplorer.findEditorsForMime(getAjxpMimeType(node));			
			if(editors.length){
				a.show();
				a._action = "open_with";
				a.update("Open");
			}else{
				a.hide();
			}			
		}else{
			a.show();
			a._action = "ls";			
			a.update("Explore");
		}
		if(items && items.length){
			var item = items[0];
			//itemPos = item.cumulativeOffset();
			itemPos = item.positionedOffset();
			itemDim = item.getDimensions();
			itemScroll = item.cumulativeScrollOffset();
			var listDisp = list._displayMode;
			mobileActions.show();
			var left;
			var container;
			if(listDisp == "thumb"){
				left = itemPos[0] + 2;
				container = $("selectable_div");
			}else{
				left = itemPos[0] + itemDim.width - 90 - 2;
				container = $("table_rows_container");
			}
			container.insert(mobileActions);
			container.setStyle({position:'relative'});
			mobileActions.setStyle({
				zIndex:list.htmlElement.style.zIndex + 1,
				position:'absolute',
				left: left + 'px',
				top:(itemPos[1]) + 2 + 'px'
			});						
		}
	});				
}

document.observe("ajaxplorer:gui_loaded", function(){
	initAjxpMobileActions();
	document.addEventListener("touchmove", function(event){
		event.preventDefault();
	});
});