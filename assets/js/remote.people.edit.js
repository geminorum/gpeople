/* global jQuery, gPeopleNetwork */

jQuery(function ($) {
  var gPeopleProfiles = [];
  var gPeopleProfilePaged = 0;

  // open modal
  $('a.gpeople-modal-open').click(function (e) {
    e.preventDefault();
    var activeRel = $(this).attr('rel');
    $('a.gpeople-modal-tab').removeClass('nav-tab-active');
    $('a.gpeople-modal-tab-' + activeRel).addClass('nav-tab-active');
    $('div.gpeople-modal-tab-content').hide();
    $('#gpeople-tab-content-' + activeRel).show();

    var options = {
      href: 'div.gpeople-modal-wrap',
      title: gPeopleNetwork.remoteEdit.modal_title,
      inline: true,
      // innerWidth: gPeopleNetwork.remoteEdit.modal_innerWidth,
      // maxHeight: gPeopleNetwork.remoteEdit.modal_maxHeight,
      // innerHeight: '85%',
      transition: 'none',
      width: '95%',
      height: '95%',
      maxWidth: '720px',
      maxHeight: '680px'
    };

    $.colorbox(options);

    $(window).resize(function () {
      $.colorbox.resize({
        width: window.innerWidth > parseInt(options.maxWidth) ? options.maxWidth : options.width,
        height: window.innerHeight > parseInt(options.maxHeight) ? options.maxHeight : options.height
      });
    });
  });

  // modal : change tabs
  $('a.gpeople-modal-tab').click(function (e) {
    e.preventDefault();
    var activeRel = $(this).attr('rel');
    $('a.gpeople-modal-tab').removeClass('nav-tab-active');
    $('a.gpeople-modal-tab-' + activeRel).addClass('nav-tab-active');
    $('div.gpeople-modal-tab-content').hide();
    $('#gpeople-tab-content-' + activeRel).fadeIn();
  });

  /// PROFILES

  // editpage: unlink profile
  $('#people_unlink_profile').click(function (e) {
    e.preventDefault();
    $('#people_profile_postid').val('0');
    $('#gpeople-profile-title').hide();
    $('#gpeople-profile-title-edit').hide();
    $('#gpeople-profile-title-none').fadeIn();
    $(this).fadeOut();
  });

  // modal : search profiles
  $('#gpeople-tab-content-profiles form').submit(function (e) {
    e.preventDefault();
    gPeopleProfilePaged = 0;
    gPeopleProfileSearch();
  });

  // modal : search profiles
  $('#gpeople-profile-groups').change(function () {
    gPeopleProfilePaged = 0;
    gPeopleProfileSearch();
  });

  // modal : search profiles form next/prev
  $('body').on('click', 'a.gpeople-data-list-profiles-next', function (e) {
    e.preventDefault();
    gPeopleProfilePaged = $(this).attr('rel');
    gPeopleProfileSearch();
  });

  var gPeopleProfileSearch = function () {
    $.ajax({
      global: false,
      dataType: 'json',
      async: false,
      url: gPeopleNetwork.remoteEdit.api,
      type: 'POST',
      data: ({
        '_ajax_nonce': gPeopleNetwork.remoteEdit.nonce,
        'action': 'gpeople_remote_people',
        'sub': 'search_profiles',
        'subsub': 'edit',
        'criteria': $('#gpeople-profile-search').val(),
        'groups': $('#gpeople-profile-groups').val(),
        'per_page': gPeopleNetwork.remoteEdit.perpage,
        'paged': gPeopleProfilePaged
      }),
      beforeSend: function () {
        $('#gpeople-people-profile-pot').empty();
        $('#gpeople-people-profile-messages').html(gPeopleNetwork.remoteEdit.loading);
      },
      success: function (response) {
        if (response.success) {
          gPeopleProfiles = response.data.list;
          $('#gpeople-people-profile-pot').html(response.data.html);
          $('#gpeople-people-profile-messages').html(response.data.message);
        } else {
          $('#gpeople-people-profile-messages').html(response.data);
        }
        $('#gpeople-people-profile-wrapper').animate({
          height: $('#gpeople-people-profile-pot').height()
        });
      }
    });
  };

  // modal : search profiles : form override all info
  $('body').on('click', 'a.gpeople-data-list-override-profile', function (e) {
    e.preventDefault();
    var setID = $(this).attr('rel');
    if (typeof gPeopleProfiles[setID] !== 'undefined') {
      $('#edittag #name').val(gPeopleProfiles[setID].title);
      $('#edittag #slug').val(gPeopleProfiles[setID].name);
      if (gPeopleProfiles[setID].has_excerpt !== false) {
        // $('#edittag #description').html(gPeopleProfiles[setID].excerpt);
        $('#edittag #description').html(gPeopleProfiles[setID].has_excerpt);
      }
      $('#people_profile_postid').val(setID);
      $('#gpeople-profile-title').html(gPeopleProfiles[setID].title).show();
      $('#gpeople-profile-title-none').hide();
      $('#gpeople-profile-title-edit').show();
      $('#people_unlink_profile').show();
      $.colorbox.close();
    }
  });

  // modal : search profiles : form change only the profile id / linked profile title
  $('body').on('click', 'a.gpeople-data-list-change-profile', function (e) {
    e.preventDefault();
    var setID = $(this).attr('rel');
    $('#people_profile_postid').val(setID);
    if (typeof gPeopleProfiles[setID] !== 'undefined') {
      $('#gpeople-profile-title').html(gPeopleProfiles[setID].title);
    }
    $.colorbox.close();
  });

  /**
  // ?!?!
  $('body').on('click','a.gpeople-data-list-add-profile',function (e) {
    e.preventDefault();
    gPeopleProfileAdd(this);
  });

  var gPeopleProfileAdd = function(el){
    setID = $(el).attr('rel');
    if( typeof gPeopleProfiles[setID] != "undefined" ){
      $('#tag-name').val(gPeopleProfiles[setID].title);
      $('#tag-slug').val(gPeopleProfiles[setID].name);
      if ( true == gPeopleProfiles[setID].has_excerpt ) {
        $('#tag-description').html(gPeopleProfiles[setID].excerpt);
      }
      $('#people_profile_postid').val(setID);

      $('.gpeople-pre-add-form').hide();
      $('#addtag').fadeIn();
    }
  };

  // modal : search profile form submit
  $("#gpeople-people-pre-add-form-profile").submit(function(e){
    e.preventDefault();
    gPeopleProfilePaged = 0;
    gPeopleProfileSearch();
  });

  // modal : search profile dropdown change
  $("#gpeople-profile-groups").change(function(){
    gPeopleProfilePaged = 0;
    gPeopleProfileSearch();
  });

  **/

  /// USERS

  // editpage: unlink profile
  $('#people_unlink_user').click(function (e) {
    e.preventDefault();
    $('#people_profile_userid').val('0');
    $('#gpeople-user-title').hide();
    $('#gpeople-user-title-edit').hide();
    $('#gpeople-user-title-none').fadeIn();
    $(this).fadeOut();
  });
});
