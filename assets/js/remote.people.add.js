jQuery(document).ready(function($) {

  var gPeopleProfiles     = [],
    gPeopleProfilePaged = 0,
    gPeopleUsers        = [],
    gPeopleUsersPaged   = 0,
    gPeopleChangeTab    = function( active ) {
      $('a.gpeople-modal-tab').removeClass('nav-tab-active');
      $('a.gpeople-modal-tab-'+active).addClass('nav-tab-active');
      $('div.gpeople-modal-tab-content').hide();
      $('#gpeople-tab-content-'+active).fadeIn();
    };

  // open modal
  $('a.gpeople-modal-open').click(function(e){
    e.preventDefault();
    gPeopleChangeTab( $(this).data('modal') );

    $.colorbox({
      href:        'div.gpeople-modal-wrap',
      title:       gPeopleNetwork.remoteAdd.modal_title,
      inline:      true,
      innerWidth:  gPeopleNetwork.remoteAdd.modal_innerWidth,
      maxHeight:   gPeopleNetwork.remoteAdd.modal_maxHeight,
      innerHeight: '85%',
      transition:  'none'
    });
  });

  // modal : change tabs
  $('a.gpeople-modal-tab').click(function(e){
    e.preventDefault();
    gPeopleChangeTab( $(this).data('tab') );
  });

  /////////////// PROFILES

  // modal: profiles : form submit
  $("#gpeople-tab-content-profiles form").submit(function(e){
    e.preventDefault();
    gPeopleProfilePaged = 0;
    gPeopleProfileSearch();
  });

  // modal: profiles : form change
  $("#gpeople-profile-groups").change(function(){
    gPeopleProfilePaged = 0;
    gPeopleProfileSearch();
    // console.log('changed : #gpeople-profile-groups');
  });

  // modal: profiles : form next/prev
  $('body').on('click','a.gpeople-data-list-profiles-next', function (e) {
    e.preventDefault();
    gPeopleProfilePaged = $(this).attr('rel');
    gPeopleProfileSearch();
  });

  var gPeopleProfileSearch = function(){
    $.ajax({
      global:   false,
      dataType: 'json',
      // async:    false,
      url:      gPeopleNetwork.api,
      type:     'POST',
      data:     ({
        '_ajax_nonce': gPeopleNetwork.remoteAdd.nonce,
        'action':      'gpeople_remote_people',
        'sub':         'search_profiles',
        'subsub':      'add',
        'criteria':    $('#gpeople-profile-search').val(),
        'group':       $('#gpeople-profile-groups').val(),
        'per_page':    gPeopleNetwork.remoteAdd.perpage,
        'paged':       gPeopleProfilePaged
      }),
      beforeSend:function(jqXHR, settings){
        $("#gpeople-people-profile-pot").html('');
        $("#gpeople-people-profile-messages").html(gPeopleNetwork.remoteAdd.loading);
      },
      success: function(response) {
        if ( true === response.success ) {
          gPeopleProfiles = response.data.list;
          $("#gpeople-people-profile-messages").html(response.data.message);
          $("#gpeople-people-profile-wrapper").fadeOut('fast',function(){
            $(this).html(response.data.html).fadeIn('fast');
          });
        } else {
          $('#gpeople-people-profile-wrapper').fadeOut('fast',function(){$(this).html('');});
          $("#gpeople-people-profile-messages").html(response.data);
        }
        //$("#gpeople-people-profile-wrapper").animate({
          //height:$("#gpeople-people-profile-pot").height()
        //});
      }
    });
  };

  $('body').on('click','a.gpeople-data-list-add-profile',function (e) {e.preventDefault();
    gPeopleProfileAdd(this);
  });

  var gPeopleProfileAdd = function(el){
    setID = $(el).attr('rel');
    if( typeof gPeopleProfiles[setID] != "undefined" ){

      $('#tag-name').val(gPeopleProfiles[setID].title);
      $('#tag-slug').val(gPeopleProfiles[setID].name);
      if ( gPeopleProfiles[setID].has_excerpt ) {

        $('textarea[name="description"]').html(gPeopleProfiles[setID].excerpt);

      }
      //console.log(gPeopleProfiles[setID]);
      $('#people_profile_postid').val(setID);
      $.colorbox.close();
    }
  };

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////


    $('#the-list').on('click', 'a.editinline', function(event){
      var tag = $(this).parents('tr').attr('id'),
        firstname = $('td.people-extra span.firstname', '#' + tag).attr('data-firstname'),
        lastname = $('td.people-extra span.lastname', '#' + tag).attr('data-lastname'),
        altname = $('td.people-extra span.altname', '#' + tag).attr('data-altname');

        $(':input[name="term-firstname"]', '.inline-edit-row').val(firstname);
        $(':input[name="term-lastname"]', '.inline-edit-row').val(lastname);
        $(':input[name="term-altname"]', '.inline-edit-row').val(altname);
    });
});
