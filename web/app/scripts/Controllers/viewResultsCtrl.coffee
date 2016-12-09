viewResultsCtrl = ($scope, $stateParams, NFLAPI, $localStorage) ->
  vm = @

  getAllRestulsByWeekID = (id) ->
    NFLAPI.GetAllRestulsByWeekID(id)
      .then (data) ->
        vm.Picks = data
        return
    return



  getRightPick = (points) ->
    if points is "3"
      return 'right-pick'
    else if points is '1'
      return 'middle-pick'
    else if points is '0'  
      return 'wrong-pick'
    else 
      return ''

  init = () ->
    vm.user = $localStorage.user
    console.log vm.user
    vm.weekID = $stateParams.weekID
    vm.week = $stateParams.week
    NFLAPI.GetWeekResults(vm.weekID)
      .then (data) ->
        vm.Results = data
        getAllRestulsByWeekID vm.weekID
        return
    return


  vm.Picks = []
  vm.Results = []

  vm.getRightPick = getRightPick

  do init
  return


angular
.module('nfl')
.controller('viewResultsCtrl', viewResultsCtrl)