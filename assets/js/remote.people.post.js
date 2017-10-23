jQuery(document).ready(function($){

  // http://stackoverflow.com/a/5721762/642752

  var
    gPeopleTerms        = [],
    gPeopleTermsPaged   = 0,
    gPeopleProfiles     = [],
    gPeopleProfilePaged = 0,
    gPeopleUsers        = [],
    gPeopleUsersPaged   = 0,
    gPeopleChangeTab    = function( active ) {
      $('a.gpeople-modal-tab').removeClass('nav-tab-active');
      $('a.gpeople-modal-tab-'+active).addClass('nav-tab-active');
      $('div.gpeople-modal-tab-content').hide();
      $('#gpeople-tab-content-'+active).fadeIn();
      $('input[focused]', '#gpeople-tab-content-'+active).focus(); // focus after changeing
    };

  // opening the modal
  $('a.gpeople-modal-open').click(function(e){
    e.preventDefault();
    /**
    var activeRel = $(this).attr('rel');
    $('a.gpeople-modal-tab').removeClass('nav-tab-active');
    $('a.gpeople-modal-tab-'+activeRel).addClass('nav-tab-active');
    $('div.gpeople-modal-tab-content').hide();
    $('#gpeople-tab-content-'+activeRel).show();
    **/
    $.colorbox({
      href:        'div.gpeople-modal-wrap',
      title:       gPeopleNetwork.remotePost.modal_title,
      inline:      true,
      innerWidth:  gPeopleNetwork.remotePost.modal_innerWidth,
      maxHeight:   gPeopleNetwork.remotePost.modal_maxHeight,
      innerHeight: '85%',
      transition:  'none'
    });

    // focus after opening
    $('input[focused]', 'div.gpeople-modal-tab-content[focused]').focus();
  });

  $('body').on('click','div.gpeople-modal-tab-content input[selectall]', function(e){
    $(this).focus().select();
  });

  // modal: change tabs
  $('a.gpeople-modal-tab').click(function(e){
    e.preventDefault();
    gPeopleChangeTab( $(this).data('tab') );
  });

  // switch the tabs
  // $('a.gpeople-modal-tab').click(function(e){
  //   e.preventDefault();
  //   var activeRel = $(this).attr('rel');
  //   $("#gpeople-people-saved-messages").html('');
  //   $('a.gpeople-modal-tab').removeClass('nav-tab-active');
  //   $('a.gpeople-modal-tab-'+activeRel).addClass('nav-tab-active');
  //   $('div.gpeople-modal-tab-content').hide();
  //   $('#gpeople-tab-content-'+activeRel).fadeIn();
  // });

  /////////////// SAVED META

  // modal: saved meta : delete row
  $('body').on('click','a.gpeople-people-delete', function (e) {
    e.preventDefault();
    var delID = $(this).attr('rel');
    $('tr.gpeople_meta_row[rel="'+delID+'"]').remove();
  });


  /////////////// PEOPLE TERMS

  // modal: search terms : form submit
  $("#gpeople-tab-content-terms form").submit(function(e){
    e.preventDefault();
    gPeopleTermsPaged = 0;
    gPeopleTermSearch();
  });

  // modal: search terms : form change
  $("#affiliation-term-id").change(function(){
    gPeopleTermsPaged = 0;
    gPeopleTermSearch();
  });

  // modal: search terms : form next/prev
  $('body').on('click','a.gpeople-data-list-terms-next', function (e) {
    e.preventDefault();
    gPeopleTermsPaged = $(this).attr('rel');
    gPeopleTermSearch();
  });

  var gPeopleTermSearch = function(){
    $.ajax({
      global:false,
      dataType:'json',
      // async: false,
      url: gPeopleNetwork.api,
      type: 'POST',
      data: ({
        '_ajax_nonce': gPeopleNetwork.remotePost.nonce,
        'action':      'gpeople_remote_people',
        'sub':         'search_terms',
        'criteria':    $('#gpeople-term-search').val(),
        'affiliation': $('#affiliation-term-id').val(),
        'per_page':    gPeopleNetwork.remotePost.perpage,
        'paged':       gPeopleTermsPaged
      }),
      beforeSend:function(){
        $("#gpeople-people-term-messages").html(gPeopleNetwork.remotePost.loading);
      },
      success: function(response) {
        if ( true === response.success ) {
          gPeopleProfiles = response.data.list;
          $("#gpeople-people-term-wrapper").fadeOut('fast',function(){
            $(this).html(response.data.html).fadeIn('fast');
          });
          $('#gpeople-people-term-messages').html(response.data.message);
        } else {
          $('#gpeople-people-term-wrapper').fadeOut('fast',function(){$(this).html('');});
          $('#gpeople-people-term-messages').html(response.data);
        }
      }
    });
  };

  // modal: search terms : form add term to saved meta
  $('body').on('click','a.gpeople-data-list-add-term',function (e) {
    e.preventDefault();
    $(this).prop('disabled', true);
    gPeopleTermInsert($(this).attr('rel'), this);
  });

  var gPeopleTermInsert = function(rel,el){
    var relClicked = $(el);
    $.ajax({
      global:false,
      dataType:'json',
      // async:false,
      url:gPeopleNetwork.api,
      type:'POST',
      data:({
        '_ajax_nonce':gPeopleNetwork.remotePost.nonce,
        'action':'gpeople_remote_people',
        'sub':'insert_term',
        'rel':rel,
      }),
      beforeSend:function(){
        $("#gpeople-people-saved-messages").html('');
        //$(el).html(gPeopleNetwork.remotePost.loading);
        $(relClicked).html(gPeopleNetwork.remotePost.spinner); // NOT WORKING!!
      },
      success: function(response) {
        if ( true === response.success ) {
          // gPeopleProfiles = response.data.list;
          $(relClicked).html(gPeopleNetwork.remotePost.added);
          $("#gpeople-meta-modal-saved-form tbody#the-list").append(response.data.row);
          $("#gpeople-meta-modal-saved-form tbody#the-list tr.no-items").hide();
          $('a.gpeople-modal-tab-saved').trigger('click');
          $("#gpeople-people-saved-messages").html(response.data.message);
        } else {
          $(relClicked).html(gPeopleNetwork.remotePost.error);
          $('a.gpeople-modal-tab-saved').trigger('click');
          $("#gpeople-people-saved-messages").html(response.data);
        }
      }
    });
  };


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
  });

  // modal: profiles : form next/prev
  $('body').on('click','a.gpeople-data-list-profiles-next', function (e) {
    e.preventDefault();
    gPeopleProfilePaged = $(this).attr('rel');
    gPeopleProfileSearch();
  });

  var gPeopleProfileSearch = function(){
    $.ajax({
      global:false,
      dataType:'json',
      // async:false,
      url:gPeopleNetwork.api,
      type:'POST',
      data:({
        '_ajax_nonce': gPeopleNetwork.remotePost.nonce,
        'action':      'gpeople_remote_people',
        'sub':         'search_profiles',
        'criteria':    $('#gpeople-profile-search').val(),
        'group':       $('#gpeople-profile-groups').val(),
        'per_page':    gPeopleNetwork.remotePost.perpage,
        'paged':       gPeopleProfilePaged
      }),
      beforeSend:function(){
        $("#gpeople-people-profile-messages").html(gPeopleNetwork.remotePost.loading);
        $('#gpeople-people-profile-wrapper').fadeOut('fast',function(){$(this).html('');});
      },
      success: function(response) {
        if ( true === response.success ) {
          gPeopleProfiles = response.data.list;
          $("#gpeople-people-profile-messages").html(response.data.message);
          $("#gpeople-people-profile-wrapper").fadeOut('fast',function(){
            $(this).html(response.data.html).fadeIn('fast');
          });
        } else {
          $("#gpeople-people-profile-messages").html(response.data);
        }
      }
    });
  };

  /////////////// USERS

  // modal: users : form submit
  $("#gpeople-tab-content-users form").submit(function(e){
    e.preventDefault();
    gPeopleUsersPaged = 0;
    gPeopleUsersSearch();
  });

  // modal: users : form change
  $("#gpeople-user-roles").change(function(){
    gPeopleUsersPaged = 0;
    gPeopleUsersSearch();
  });

  // modal: users : form next/prev
  $('body').on('click','a.gpeople-data-list-users-next', function (e) {
    e.preventDefault();
    gPeopleUsersPaged = $(this).attr('rel');
    gPeopleUsersSearch();
  });

  var gPeopleUsersSearch = function(){
    $.ajax({
      global:false,
      dataType:'json',
      // async:false,
      url:gPeopleNetwork.api,
      type:'POST',
      data:({
        '_ajax_nonce': gPeopleNetwork.remotePost.nonce,
        'action':      'gpeople_remote_people',
        'sub':         'search_users',
        'criteria':    $('#gpeople-user-search').val(),
        'roles':       $('#gpeople-user-roles').val(),
        'per_page':    gPeopleNetwork.remotePost.perpage,
        'paged':       gPeopleUsersPaged
      }),
      beforeSend:function(){
        // $("#gpeople-people-user-pot").html('');
        $("#gpeople-people-user-messages").html(gPeopleNetwork.remotePost.loading);
      },
      success: function(response) {
        if ( true === response.success ) {
          gPeopleProfiles = response.data.list;
          //$("#gpeople-people-user-pot").html(response.data.html);
          $("#gpeople-people-user-wrapper").html(response.data.html);
          $("#gpeople-people-user-messages").html(response.data.message);
        } else {
          $("#gpeople-people-user-messages").html(response.data);
        }
        //$("#gpeople-people-user-wrapper").animate({
           // height:$("#gpeople-people-user-pot").height()
        //});
      }
    });
  };


  /////////////// MANUAL NEW PEOPLE TERM

  $("#gpeople-tab-content-manual form").submit(function(e){
    e.preventDefault();
    $.ajax({
      global:false,
      dataType:'json',
      // async:false,
      url:gPeopleNetwork.api,
      type:'POST',
      data:({
        '_ajax_nonce': gPeopleNetwork.remotePost.nonce,
        'action':      'gpeople_remote_people',
        'sub':         'insert_manual',
        'term':        $(this).serializeArray()
      }),
      beforeSend:function(){
        // $("#gpeople-people-user-pot").html('');
        // $("#gpeople-people-manual-messages").html(gPeopleNetwork.remotePost.loading);
        $("#gpeople-people-saved-messages").html('');
        $("#gpeople-people-manual-messages").html('');
      },
      success: function(response) {
        if ( true === response.success ) {
          // gPeopleProfiles = response.data.list;
          // $("#gpeople-people-user-pot").html(response.data.html);
          // $("#gpeople-people-user-messages").html(response.data.message);
          $("#gpeople-meta-modal-saved-form tbody#the-list").append(response.data.row);
          $("#gpeople-meta-modal-saved-form tbody#the-list tr.no-items").hide();
          $('#gpeople-tab-content-manual form').trigger("reset");
          $('a.gpeople-modal-tab-saved').trigger('click');
          $("#gpeople-people-saved-messages").html(response.data.message);

        } else {
          $("#gpeople-people-manual-messages").html(response.data);
        }
      }
    });
  });

  $('#gpeople-meta-modal-saved-form').submit(function() {

    $(this).ajaxSubmit({
      url:gPeopleNetwork.api,
      dataType:'json',
      data:({
        '_ajax_nonce': gPeopleNetwork.remotePost.nonce,
        'action':      'gpeople_remote_people',
        'sub':         'store_meta',
        // 'term':        $(this).serializeArray()
      }),
      beforeSubmit: function () {
        $("#gpeople-people-saved-messages").html('');
        $('gpeople_meta_save_close').val(gPeopleNetwork.remotePost.spinner);
      },
      success: function(response) {
        if ( true === response.success ) {
          //$("#gpeople-people-saved-messages").html(response.data);
          $("#gpeople_saved_byline").html(response.data).fadeIn('fast');
          $.colorbox.close();
        } else {
          $("#gpeople-people-saved-messages").html(response.data);
        }
      }
    });

    return false;
  });

  /**
  $('#gpeople-meta-modal-saved-form').ajaxForm({
    url:gPeopleNetwork.api,
    data:({
      '_ajax_nonce':gPeopleNetwork.remotePost.nonce,
      'action':'gpeople_remote_people',
      'sub':'store_meta',
      // 'term':$(this).serializeArray()
    }),
    dataType:'json',
    beforeSubmit: function () {
      $("#gpeople-people-saved-messages").html('');
      $('gpeople_meta_save_close').val(gPeopleNetwork.remotePost.loading);
      alert('hilo!');
    },
    success: function(response) {
      console.debug( response );
      if ( response.success == true ) {
        //$("#gpeople-people-saved-messages").html(response.data);
        $("#gpeople_saved_byline").html(response.data);
        $.colorbox.close();
      } else {
        $("#gpeople-people-saved-messages").html(response.data);
      }
    }
  });
  **/

  // http://www.jqueryrain.com/2012/12/prevent-multiple-submit-of-your-form-with-jquery/
  // http://www.jqueryrain.com/2012/12/jquery-highlight-related-label-when-input-in-focus/

});
