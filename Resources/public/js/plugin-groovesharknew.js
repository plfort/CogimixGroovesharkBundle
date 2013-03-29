function groovesharkPlayer(musicPlayer){
this.name = "Grooveshark";
this.interval;
this.musicPlayer = musicPlayer;
this.currentState = null;
this.soundmanagerPlayer = soundManager;

this.currentSoundObject=null;
var self = this;
self.musicPlayer.cursor.progressbar();
this.play = function(item) {
	var songId = item.entryId;
	$.get( Routing.generate('_grooveshark_getsong',{'songId':songId}),function(response) {
	    if(response.success==true){
	    	self.currentSoundObject=self.soundmanagerPlayer.createSound({
	  		  id: songId.toString(),
	  		  url: response.data.stream.url,
	  		  autoLoad: true,
	  		  autoPlay: true,
	  		  volume: 50,
	  		  onload: function() {
	  			self.currentSoundObject.played30sec = false;
	  			this.onPosition(30000, function(eventPosition) {
	  				self.currentSoundObject.played30sec = true;
	  		      console.log('Grooveshark mark30sec !');
	  		    $.get(Routing.generate('_grooveshark_markStreamKeyOver30Sec',{streamKey:response.data.stream.StreamKey,serverId:response.data.stream.StreamServerID}),
	  					  function(response){});
	  			});
	  			  self.musicPlayer.enableControls();
	  			  self.musicPlayer.cursor.slider("option", "max", response.data.stream.uSecs/1000).progressbar();			  
	  			  self.musicPlayer.bindCursorStop(function(value) {
	  				  
	  				  self.currentSoundObject.setPosition(value);
	  				});
	  		  },
	  		  onstop: function(){
	  			 this.destruct();
	  			  self.musicPlayer.cursor.slider("option", "max", 0).progressbar('value',0);
	  		  },
	  		  onfinish: function(){
	  			  if(self.currentSoundObject.played30sec == true){
	  			  $.get(Routing.generate('_grooveshark_markSongComplete',{streamKey:response.data.stream.StreamKey,serverId:response.data.stream.StreamServerID,songId:songId}),
	  					  function(response){});
	  			  }
	  			  this.destruct();
	  			  self.musicPlayer.next();
	  		  },
	  		  whileloading: function(){
	  			
	  			  self.musicPlayer.cursor.progressbar('value',(this.bytesLoaded/this.bytesTotal)*100 );
	  		  },
	  		  whileplaying: function(){
	  			if(self.musicPlayer.cursor.data('isdragging')==false){
	  			  self.musicPlayer.cursor.slider("value", this.position);
	  			}
	  		  },
	  		  
	  		  
	  		});
			       
	    }
	},'json');
	

};
this.stop = function(){
	console.log('call stop soundmanager');	
	self.currentSoundObject.stop();	
}

this.pause = function(){
	console.log('call pause soundmanager');
	self.currentSoundObject.pause();
	
}
this.resume = function(){
	console.log('call resume soundmanager');
	self.currentSoundObject.resume();
}
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
	 $("#loginGroovesharkBtn").click(function(event){
    	 $.get(Routing.generate('_grooveshark_login'),
            	 function(response){
    		    if(response.success==true){
    		    	 $("#loginGroovesharkModal > .modal-body").html(response.data.htmlForm);
    		    	 $("#loginGroovesharkModal").modal('toggle');
        		    }
        	 },'json');
    	    return false;
	 });

	 $("#loginGroovesharkModal").on('submit','form',function(event){
		var postData = $(this).serialize();
		 $.post(Routing.generate('_grooveshark_login'),
				 postData,
        		 function(response){
			    if(response.success==true){
			    	 $("#loginGroovesharkModal").modal('toggle');
			    	 $("#loginGroovesharkBtn").after(response.data.playlistsHtml);
			    	 $(".gs-playlist-item").draggable(draggableOptionsPlaylistListItem);
			    	 $("#loginGroovesharkBtn").remove();
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
                console.log(response.data.tracks);
                callback(response.data.tracks);
                }
            },'json');
	
}