{"version":3,"file":"sectionmanager.bundle.map.js","names":["this","BX","exports","calendar_util","calendar_sectionmanager","main_core","main_core_events","CalendarSection","constructor","data","updateData","calendarContext","Util","getCalendarContext","getId","id","type","CAL_TYPE","ownerId","parseInt","OWNER_ID","ID","color","COLOR","name","NAME","isShown","sectionManager","sectionIsShown","show","hiddenSections","getHiddenSections","filter","sectionId","setHiddenSections","saveHiddenSections","hide","push","remove","EventAlias","getBX","Event","EventEmitter","emit","BaseEvent","ajax","runAction","then","response","updateListAfterDelete","hideSyncSection","onCustomEvent","calendar","sectionStatus","hideExternalCalendarSection","getLink","LINK","canBeConnectedToOutlook","isPseudo","OUTLOOK_JS","CAL_DAV_CAL","CAL_DAV_CON","browser","IsMac","connectToOutlook","url","result","eval","canDo","action","isVirtual","includes","hasPermission","PERM","isSuperposed","SUPERPOSED","indexOf","GAPI_CALENDAR_ID","EXTERNAL_TYPE","isGoogle","googleTypes","isCalDav","isIcloud","isOffice365","isArchive","isExchange","isCompanyCalendar","hasConnection","connectionLinks","length","isLocationRoom","belongsToView","getCalendarType","getOwnerId","belongsToOwner","belongsToUser","getUserId","userId","ACTIVE","getExternalType","getConnectionLinks","Type","isArray","externalTypeIsLocal","SectionManager","EXTERNAL_TYPE_LOCAL","isPrimaryForConnection","find","connection","isPrimary","isActive","getType","getConnectionIdList","connectionIdList","connectionId","reload","section","i","sections","CalendarTaskSection","defaultColor","belongToUser","defaultName","Loc","getMessage","super","edit_section","view_full","view_time","view_title","isUserTaskSection","taskSectionBelongToUser","config","setSections","setConfig","addTaskSection","sortSections","subscribeOnce","event","deleteSectionHandler","reloadDataDebounce","Runtime","debounce","reloadData","RELOAD_DELAY","rawSections","sectionIndex","forEach","sectionData","sort","a","b","isFunction","localeCompare","index","calendarType","ownerName","defaultSectionAccess","new_section_access","sectionAccessTasks","showTasks","customizationData","sectionCustomization","meetSectionId","taskSection","handlePullChanges","params","command","fields","getSections","getSuperposedSectionList","getSectionListForEdit","getSection","getDefaultSectionName","getDefaultSectionAccess","saveSection","access","Promise","resolve","_params$section","isString","trim","isCustomization","analyticsLabel","customization","external_type","sectionList","Calendar","displayError","errors","util","in_array","optionName","userIsOwner","userOptions","save","getSectionsInfo","allActive","superposed","active","hidden","undefined","deleteFromArray","static","isExternalMode","getDefaultSection","newEntrySectionId","options","followedUserList","trackingUsersList","getFollowedUserList","sectionGroups","title","user","htmlspecialchars","FORMATTED_NAME","getSectionAccessTasks","isNumber","defaultSectionId","item","section1","section2","setDefaultSection","userSettings","getUserSettings","key","defaultSections","setUserSettings","sectionExternalType","linkList","provider","syncInterface","getProviderById"],"sources":["sectionmanager.bundle.js"],"mappings":"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"}