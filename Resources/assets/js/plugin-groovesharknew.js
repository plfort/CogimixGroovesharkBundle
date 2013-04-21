function groovesharkPlayer(musicPlayer){
this.name = "Grooveshark";
this.cancelRequested = false;
this.interval;
this.musicPlayer = musicPlayer;
this.currentState = null;
this.soundmanagerPlayer = soundManager;
this.widgetElement =$("#groovesharkWidgetContainer");
this.currentSoundObject=null;
this.currentSongId = null;
this.timeout30Sec = null;
this.timeoutGetSong = null;
var self = this;
self.musicPlayer.cursor.progressbar();

this.requestCancel=function(){
	self.cancelRequested=true;
	if(self.currentSoundObject != null){
		loggerGrooveshark.debug('requestCanel currentSoundObject === null');
		self.currentSoundObject.destruct();
		self.cancelRequested=false;
	}
}

this.play = function(item) {
	var songId = item.entryId;
	self.currentSongId = songId;
	loggerGrooveshark.debug('Call play, cancelRequsted : '+item.title, songId);
	if(self.timeoutGetSong !==null){
		self.timeoutGetSong.clear();
	}
	self.timeoutGetSong = new Timer(function(){
	$.get( Routing.generate('_grooveshark_getsong',{'songId':songId}),function(response) {
	    if(response.success==true){
	    	var uSecs= response.data.stream.uSecs;
	    	
	    	self.currentSoundObject=self.soundmanagerPlayer.createSound({
	  		  id: 'gs'+songId,
	  		  multiShot : false,
	  		  url: response.data.stream.url,
	  		  autoLoad: true,
	  		  autoPlay: true,
	  		  volume: self.musicPlayer.volume,
	  		  onload: function() {
	  			 loggerGrooveshark.debug("ONLOAD");
	 			
	  			  //self.musicPlayer.enableControls();
	  			 // self.musicPlayer.cursor.slider("option", "max", Math.round(uSecs/1000)).progressbar();			  
	  			 /* self.musicPlayer.bindCursorStop(function(value) {
	  				  
	  				  self.currentSoundObject.setPosition(value);
	  				});*/
	  		  },
	  		  onplay:function(){
	  			  self.musicPlayer.enableControls();
	  			  if(songId == self.currentSongId && self.musicPlayer.currentPlugin.name == self.name){
		  			  if(self.timeout30sec == null){
		  				 loggerGrooveshark.debug("create timer");
			  			  self.timeout30Sec=new Timer(function() {
				  				
					  		     loggerGrooveshark.debug('Grooveshark mark30sec !');
					  		    $.get(Routing.generate('_grooveshark_markStreamKeyOver30Sec',{streamKey:response.data.stream.StreamKey,serverId:response.data.stream.StreamServerID}),
					  					  function(response){});
					  			},30000);
		  			  }
		  			
		  			  self.musicPlayer.cursor.slider("option", "max", Math.round(uSecs/1000000)).progressbar();
		  			  self.musicPlayer.bindCursorStop(function(value) {
		  				  
		  				  self.currentSoundObject.setPosition(value*1000);
		  				});
	  			  }else{
	  				  this.destruct();
	  			  }
	  			 
	  		  },
	  		  onresume:function(){
	  			  if(self.timeout30Sec !==null){
	  				  self.timeout30Sec.resume();
	  			  }
	  		  },
	  		  onpause: function(){
	  			loggerGrooveshark.debug("ONPAUSE");
	  			self.timeout30Sec.pause();
	  		  },
	  		  onstop: function(){
	  			loggerGrooveshark.debug("ONSTOP");
	  			 this.destruct();
	  			 if(self.timeout30Sec !== null){
	  				 self.timeout30Sec.clear();
	  			 }
	  			
	  			 self.timeout30Sec = null;
 				 self.musicPlayer.cursor.slider('value', 0).progressbar('value',0);
	  			 
	  		  },
	  		  onfinish: function(){
	  			loggerGrooveshark.debug("ONFINISH");
	  			  
	  			  $.get(Routing.generate('_grooveshark_markSongComplete',{streamKey:response.data.stream.StreamKey,serverId:response.data.stream.StreamServerID,songId:songId}),
	  					  function(response){});
	  			  
	  			  this.destruct();
	  			  self.musicPlayer.next();
	  		  },
	  		  whileloading: function(){
	  			
	  			  self.musicPlayer.cursor.progressbar('value',(this.bytesLoaded/this.bytesTotal)*100 );

	  		  },
	  		  whileplaying: function(){
	  		
	  			if(songId != self.currentSongId){
	  				this.destruct();
	  				return;
	  			}
	  			
			  	if(self.musicPlayer.cursor.data('isdragging')==false){
			  		
			  		self.musicPlayer.cursor.slider("value", Math.round(this.position/1000));
			  	}
	  			
	  		  },
	  		  
	  		});
			       
	    }
	},'json');
	},1000);



};
this.stop = function(){
	loggerGrooveshark.debug('call stop soundmanager');	
	 self.currentSongId=null;
	if(self.currentSoundObject!=null){
		loggerGrooveshark.debug('-- currentSoundObject !== null');
		
		self.currentSoundObject.stop();	
		if(self.timeout30Sec !== null){
				 self.timeout30Sec.clear();
			 }
	}else{
		loggerGrooveshark.debug('-- currentSoundObject === null');
	}
}

this.pause = function(){
	loggerGrooveshark.debug('call pause soundmanager');
	if(self.currentSoundObject!=null){
		self.currentSoundObject.pause();
	}
	
}

this.resume = function(){
	loggerGrooveshark.debug('call resume soundmanager');
	if(self.currentSoundObject!=null){
		self.currentSoundObject.resume();
	}
}
this.setVolume = function(value){
	loggerGrooveshark.debug('call setvolume soundmanager');
	if(self.currentSoundObject!=null){
		self.currentSoundObject.setVolume(value);
	}
}
}

$("body").on('musicplayerReady',function(event){
	event.musicPlayer.addPlugin('gs',new groovesharkPlayer(event.musicPlayer));
});

$(document).ready(function(){
	
	$(document).on('click','#loginGroovesharkBtn',function(event){
		$("#modalLoginGroovehsark").modal("toggle");
	});
	
	$("#playlist-container").on('click','.showPlaylistGroovesharkBtn',function(event){
		var playlistElement = $(this).closest('.gs-playlist-item');
		var playlistName = $(this).html();
		var playlistAlias = playlistElement.data('alias');
		$.get(Routing.generate('_grooveshark_playlist_songs',{'id':playlistElement.data('id')}),function(response){
			if(response.success == true){
				renderResult(response.data.tracks,{tpl:'trackNoSortTpl',tabName:playlistName,alias:playlistAlias});
            	$("#wrap").animate({scrollTop:0});
	
			}else{
				loggerGrooveshark.debug('Error with grooveshark');
			}
		},'json');
		return false;
	});
	
	$("#playlist-container").on('click','.playPlaylistGroovesharkBtn',function(event){
		
		$.get(Routing.generate('_grooveshark_playlist_songs',{'id':$(this).closest('.gs-playlist-item').data('id')}),function(response){
			if(response.success == true){
				musicPlayer.removeAllSongs();
				musicPlayer.addSongs(response.data.tracks);
                musicPlayer.play();
			}else{
				loggerGrooveshark.debug('Error with grooveshark');
			}
		},'json');
		return false;
	});
	$("#grooveshark-menu").on('click','#loginGroovesharkBtn',function(event){
	 	 $.get(Routing.generate('_grooveshark_login'),
            	 function(response){
    		    if(response.success==true){
    		    	 $("#loginGroovesharkModal > .modal-body").html(response.data.htmlForm);
    		    	 $("#loginGroovesharkModal").modal('toggle');
        		    }
        	 },'json');
    	    return false;
		
	});
	
	$("#grooveshark-menu").on('click','#logoutGroovesharkBtn',function(event){
		var currentItem = $(this);
	 	 $.get(Routing.generate('_grooveshark_logout'),
           	 function(response){
   		    if(response.success==true){
   		    	currentItem.replaceWith(response.data.loginLink);
   		        $("#gs-playlist-container").empty();
       		    }
       	 },'json');
   	    return false;
		
	});

	 $("#loginGroovesharkModal").on('submit','form',function(event){
		var postData = $(this).serializeArray();
		
		$.each(postData,function(key,input){
			if(input.name =='cogimix_grooveshark_login[password]'){
				input.value=hex_md5(input.value);
			}
		});
		 $.post(Routing.generate('_grooveshark_login'),
				 postData,
        		 function(response){
			    if(response.success==true){
			    	 $("#loginGroovesharkModal").modal('toggle');
			    	 $("#gs-playlist-container").empty();
			    	 $("#gs-playlist-container").replaceWith(response.data.playlistsHtml);
			    	 $(".gs-playlist-item").draggable(draggableOptionsPlaylistListItem);
			    	 $("#loginGroovesharkBtn").replaceWith(response.data.logoutLink);
    			}else{
    				 $("#loginGroovesharkModal > .modal-body").html(response.data.htmlForm);
        		}
    		 },'json');

		    return false;
     });
    $(".gs-playlist-item").draggable(draggableOptionsPlaylistListItem);
});


droppedHookArray['gs-playlist'] = function(droppedItem,callback){
		var playlistId=droppedItem.data('id');
		$.get(Routing.generate('_grooveshark_playlist_songs',{'id':playlistId}),function(response){
            if(response.success==true){
                loggerGrooveshark.debug(response.data.tracks);
                callback(response.data.tracks);
                }
            },'json');
	
}