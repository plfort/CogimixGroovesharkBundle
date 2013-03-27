function groovesharkPlayer(musicPlayer){
this.name = "Grooveshark";
var interval;
this.musicPlayer=musicPlayer;
this.duration;
this.currentState=null;
this.gsplayer=null;
this.widgetElement = null;
var self = this;

swfobject.embedSWF(location.protocol +"//grooveshark.com/APIPlayer.swf", "gsplayer", "1", "1", "9.0.0", "", {}, {allowScriptAccess: "always"}, {id:"gsplayer", name:"gsplayer"}, function(e) {
    var element = e.ref;
    if (element) {
    	 self.gsplayer = element;
    	 setTimeout(function() {
    		 console.log("set status callback");
    		 self.gsplayer.setStatusCallback("onPlayerStateChange");
    		 self.gsplayer.setPositionCallback("onPositionChange");
    		 
    	 },1500);
    }else {
        alert('Cannot load Grooveshark player');
      }
});

	this.stop=function(){
		console.log('stop gsplayer');
		self.currentState='stopped';
		self.gsplayer.stopStream();
	};
	this.pause=function(){
		console.log('pause gsplayer');
		self.gsplayer.pauseStream();
		
	};
	this.resume=function(){
		console.log('resume gsplayer');
		self.gsplayer.resumeStream();
	};
	
	
	
	this.play=function(item){
		console.log('gs call play '+item.entryId);

	    	var songId = item.entryId;
	    			console.log('gsplayer already init');
	    			self.currentState='playing';
	    			$.get( Routing.generate('_grooveshark_getsong',{'songId':songId}),function(response) {
		    		    if(response.success==true){
		    		    	
		     			        self.gsplayer.playStreamKey(response.data.stream.StreamKey, response.data.stream.StreamServerHostname, response.data.stream.StreamServerID);      
		     			        self.duration = response.data.stream.uSecs/1000;
		    		    }
		    		},'json');
		};

	this.playHelper= function(){
		self.ytplayer.playVideo();
	 
	};

	
	
	this.onPlayerStateChange=function(newState){
		console.log('State changed GROOVESHARK '+newState+' currentPlayer state : '+self.currentState);
		if(self.currentState!='stopped'){
			var oldState=self.currentState;
			
			self.currentState=newState;
			if(newState == 'playing'){
				self.musicPlayer.enableControls();
				console.log('set duration : '+self.duration);
				self.musicPlayer.cursor.slider("option","max",self.duration);
			}
			
			if(newState == 'completed' ){
				self.musicPlayer.next();
				return;
			}
		}else{
			self.stop();
		}
	}
	
	this.onPositionCallback=function(current,duration){
		
		if(!self.musicPlayer.cursor.isDragging){	
			self.musicPlayer.cursor.slider("value",current);
		}
	}
}

function onPlayerStateChange(newState){
	
	musicPlayer.plugin['gs'].onPlayerStateChange(newState);
}
function onPositionChange(position){
	
	musicPlayer.plugin['gs'].onPositionCallback(position.position,position.duration);
}
$(document).ready(function(){

	$.get('bundles/cogimixgrooveshark/js/template/track.html',function(html){
		tplFiles['trackGs']=html;
	},'text');
	
	$(document).on('click','#loginGroovesharkBtn',function(event){
		$("#modalLoginGroovehsark").modal("toggle");
	});
	
	$("#playlist-container").on('click','.showPlaylistGroovesharkBtn',function(event){
		
		$.get(Routing.generate('_grooveshark_playlist_songs',{'id':$(this).closest('.gs-playlist-item').data('id')}),function(response){
			if(response.success == true){
				renderResult(response.data.tracks,{tpl:'trackGs'});
            	$("#wrap").animate({scrollTop:0});
	
			}else{
				console.log('Error with grooveshark');
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
				console.log('Error with grooveshark');
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
                console.log(response.data.tracks);
                callback(response.data.tracks);
                }
            },'json');
	
}