{"version":3,"file":"registry.bundle.map.js","names":["this","BX","Messenger","Embedding","Provider","exports","main_core_events","rest_client","im_oldChatEmbedding_application_core","im_oldChatEmbedding_const","im_oldChatEmbedding_lib_logger","RecentService","static","instance","constructor","store","restClient","dataIsPreloaded","itemsPerPage","isLoading","pagesLoaded","hasMoreItemsToLoad","lastMessageDate","Core","getStore","getRestClient","onUpdateStateHandler","onUpdateState","bind","EventEmitter","subscribe","EventType","recent","updateState","getCollection","getters","loadFirstPage","ignorePreloadedItems","Logger","warn","Promise","resolve","requestItems","firstPage","loadNextPage","setPreloadedData","params","items","hasMore","getLastMessageDate","updateModels","hideChat","dialogId","recentItem","dispatch","id","callMethod","RestMethod","imRecentHide","DIALOG_ID","catch","error","console","queryParams","getQueryParams","getQueryMethod","then","result","data","imRecentList","SKIP_OPENLINES","LIMIT","LAST_MESSAGE_DATE","GET_ORIGINAL_TEXT","rawData","users","dialogues","prepareDataForModels","usersPromise","botList","dialoguesPromise","recentPromise","all","birthdayList","forEach","item","user","isAddedAlready","push","chat","prepareGroupChat","prepareChatForAdditionalUser","existingRecentItem","options","default_user_record","prepareChatForUser","getBirthdayPlaceholder","type","some","counter","chatId","chat_id","avatar","color","name","DialogType","birthdayPlaceholder","length","slice","message","date","Service","Event","Application","Const","Lib"],"sources":["registry.bundle.js"],"mappings":"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"}