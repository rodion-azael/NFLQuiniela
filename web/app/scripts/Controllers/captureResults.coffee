captureResults = ($scope, $stateParams, NFLAPI) ->
  vm = @



  saveResult = (i) ->
    obj = 
      MatchID: vm.Results[i].MatchID
      HomePts: vm.Results[i].HomePts
      AwayPts: vm.Results[i].AwayPts
      HomeID: vm.Results[i].HomeTeamID
      AwayID: vm.Results[i].AwayTeamID
      WeekID: vm.weekID
    NFLAPI.SaveNewResult(obj)
      .then (data) ->
        if(data.success)
          alert data.message
        else
          alert "Ha ocurrido un error."
        return
    return

  # ----- #
  init = () ->
    vm.weekID = $stateParams.weekID
    vm.week = $stateParams.week
    NFLAPI.GetWeekResults(vm.weekID)
      .then (data) ->
        vm.Results = data
        return
    return

  vm.Results = []
  vm.responseMsg = ''

  vm.saveResult = saveResult

  do init
  return


angular
.module('nfl')
.controller('captureResults', captureResults)