weeksCtrl = ($scope, NFLAPI, $localStorage, $state) ->
  vm = @

  init = () ->
    $scope.$parent.vm.Section = 'weeks'
    vm.season = 1
    NFLAPI.GetAllSeasons()
      .then (data) ->
        vm.Seasons = data
        return
    vm.user = $localStorage.user
    do changeSeason
    return

  # ----- #
  changeSeason = () ->
    NFLAPI.GetAllWeeks(vm.season)
      .then (data) ->
        if data.relogin
          $localStorage.$reset();
          $state.go 'login'
        vm.Weeks = data;
        return
      return


  # ----- #
  deleteWeek = (weekID) ->
    NFLAPI.DeleteWeek(weekID)
      .then (data) ->
        if data.success
          tempWeeks = []
          tempWeeks.push(week) for week in vm.Weeks when week.WeekID isnt weekID
          vm.Weeks = tempWeeks 
        else
          console.log data
        return
    return

  
  # ----- #
  saveNewWeek = () ->
    if !vm.newWeekNumber or !vm.newWeekStart or !vm.newWeekEnd
      alert 'Todos los campos son requeridos'
      return false
    obj =
      number: vm.newWeekNumber
      start: moment(vm.newWeekStart).format('YYYY-MM-DD HH:mm:ss')
      end: moment(vm.newWeekEnd).format('YYYY-MM-DD HH:mm:ss')
      season: vm.season
    NFLAPI.SaveNewWeek(obj)
      .then (data) ->
        vm.Weeks.shift data.newWeek[0]
        vm.newWeekStart = ''
        vm.newWeekEnd = ''
        vm.newWeekNumber = ''
    return

  canEdit = () ->
    if vm.user.IsAdmin is '1' then return true
    return false

  vm.Weeks = {}
  vm.dateTimePickerOption = 
    icons:
      next:'glyphicon glyphicon-arrow-right'
      previous:'glyphicon glyphicon-arrow-left'
      up:'glyphicon glyphicon-arrow-up'
      down:'glyphicon glyphicon-arrow-down'
  

  vm.saveNewWeek = saveNewWeek
  vm.deleteWeek = deleteWeek
  vm.changeSeason = changeSeason
  vm.canEdit = canEdit
  do init
  return


angular
  .module('nfl')
  .controller('weeksCtrl', weeksCtrl)