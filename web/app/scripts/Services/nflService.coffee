apiNFL = ($http, $q, $localStorage) ->
  url = 'http://93.188.162.97/apiNFL/v1/'
  $http.defaults.withCredentials = true;
  #token = if $localStorage.user and $localStorage.user['token'] then $localStorage.user['token'] else false

  _config = 
      headers:
        'Content-Type': 'application/x-www-form-urlencoded'   

  # ---- #
  logIn = (obj) ->
    defered = $q.defer()
    
    obj = $.param obj
    $http.post(url + 'login', obj, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ---- #
  saveNewWeek = (obj) ->
    defered = $q.defer()
    
    obj = $.param obj
    $http.post(url + 'saveNewWeek', obj, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ---- #
  getAllWeeks = (season, order) ->
    order = if order  then order else 'DESC'
    defered = $q.defer()
    $http.get(url + 'getAllWeeks/' + season + '/' + order, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ----- #
  getTotalResultsBySeasonID = (id) -> 
    defered = $q.defer()
    $http.get(url + 'getTotalResultsBySeasonID/' + id, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise    

  # ----- #
  getAllTeams = () ->
    defered = $q.defer()
    $http.get(url + 'getAllTeams', _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ----- #
  getMatchsByWeekID = (id) ->
    defered = $q.defer()
    $http.get(url + 'getAllMatchByWeek/' + id, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ----- #
  deleteWeek = (weekID) ->
    defered = $q.defer()
    $http.get(url + 'deleteWeek/' + weekID, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ------ #
  saveNewMatch = (obj) ->
    defered = $q.defer()
    
    obj = $.param obj
    $http.post(url + 'saveNewMatch', obj, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ----- #
  getUserInputsByWeekId = (weekID) ->
    defered = $q.defer()
    $http.get(url + 'getUserInputsByWeekId/' + weekID, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise


  # ----- #
  getAllRestulsByWeekID = (weekID) ->
    defered = $q.defer()
    $http.get(url + 'getAllRestulsByWeekID/' + weekID, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise


  # ----- #
  saveInputs = (weekID, obj) ->
    defered = $q.defer()
    
    obj = $.param obj
    $http.post(url + 'saveInputs/' + weekID, obj, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ----- #
  getAllSeasons = () ->
    defered = $q.defer()
    
    $http.get(url + 'getAllSeasons', _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise   

  # ----- #
  getWeekResults = (id) ->
    defered = $q.defer()
    
    $http.get(url + 'getWeekResults/'+ id, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise    

  # ----- #
  saveNewPass = (obj) ->
    defered = $q.defer()
    
    obj = $.param obj
    $http.post(url + 'saveNewPass', obj, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise

  # ----- #
  saveNewResult = (obj) ->
    defered = $q.defer()
    
    obj = $.param obj
    $http.post(url + 'saveNewResult', obj, _config)
        .success (data) ->
          defered.resolve data
        .error (err) ->
          defered.reject err
    defered.promise



  return {
    LogIn : logIn
    GetAllWeeks: getAllWeeks
    SaveNewWeek: saveNewWeek
    DeleteWeek: deleteWeek
    GetAllTeams: getAllTeams
    GetMatchsByWeekID: getMatchsByWeekID
    SaveNewMatch: saveNewMatch
    GetUserInputsByWeekId: getUserInputsByWeekId
    SaveInputs: saveInputs
    GetAllSeasons: getAllSeasons
    SaveNewPass: saveNewPass
    GetWeekResults: getWeekResults
    SaveNewResult: saveNewResult
    GetAllRestulsByWeekID: getAllRestulsByWeekID
    GetTotalResultsBySeasonID: getTotalResultsBySeasonID
  }


angular
	.module('nfl')
	.service('NFLAPI', apiNFL)
