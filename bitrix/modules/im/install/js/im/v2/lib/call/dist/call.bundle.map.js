{"version":3,"file":"call.bundle.map.js","names":["this","BX","Messenger","v2","exports","main_core_events","ui_vue3_vuex","im_call","im_public","im_v2_application_core","im_v2_lib_slider","im_v2_lib_logger","im_v2_lib_soundNotification","im_v2_lib_rest","im_v2_const","BetaCallService","static","chatId","runAction","RestMethod","imCallBetaCreateRoom","data","_controller","babelHelpers","classPrivateFieldLooseKey","_store","_getController","_subscribeToEvents","_onCallCreated","_onCallJoin","_onCallLeave","_onCallDestroy","_onOpenChat","_checkCallSupport","_checkUserCallSupport","_checkChatCallSupport","_pushServerIsActive","_getCurrentDialogId","CallManager","instance","getInstance","constructor","Object","defineProperty","value","_getCurrentDialogId2","_pushServerIsActive2","_checkChatCallSupport2","_checkUserCallSupport2","_checkCallSupport2","_onOpenChat2","_onCallDestroy2","_onCallLeave2","_onCallJoin2","_onCallCreated2","_subscribeToEvents2","_getController2","writable","classPrivateFieldLooseBase","Core","getStore","createBetaCallRoom","createRoom","startCall","dialogId","withVideo","Logger","warn","joinCall","callId","leaveCurrentCall","foldCurrentCall","hasActiveCall","hasVisibleCall","fold","unfoldCurrentCall","unfold","getCurrentCallDialogId","currentCall","associatedEntity","id","hasCurrentCall","hasCurrentScreenSharing","isScreenSharingStarted","startTest","test","chatCanBeCalled","dialog","getters","isChat","type","DialogType","user","callAllowed","ChatOption","call","callSupported","isAnnouncement","announcement","isExternalTelephonyCall","Controller","init","language","getLanguageId","messengerFacade","getDefaultZIndex","MessengerSlider","getZIndex","isMessengerOpen","isOpened","isSliderFocused","isFocused","isThemeDark","openMessenger","openChat","openHistory","openSettings","openHelpArticle","getContainer","document","querySelector","viewContainerClass","getMessageCount","getCurrentDialogId","isPromoRequired","repeatSound","soundType","timeout","force","SoundNotificationManager","playLoop","stopRepeatSound","stop","events","EventEmitter","subscribe","EventType","layout","onOpenChat","bind","onOpenNotifications","event","getData","addEventListener","Call","Event","onJoin","onLeave","onDestroy","dispatch","name","state","RecentCallStatus","waiting","fields","joined","callDialogId","openedChat","Util","isWebRTCSupported","userId","Number","parseInt","status","bot","network","getUserId","lastActivityDate","userCounter","getUserLimit","Layout","chat","entityId","Lib","Vue3","Vuex","Application","Const"],"sources":["call.bundle.js"],"mappings":"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"}