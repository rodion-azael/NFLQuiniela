globalResultsCtrl = ($scope, $stateParams, NFLAPI, $localStorage) ->
  vm = @


  # ----- #
  changeSeason = () ->
    NFLAPI.GetAllWeeks(vm.season, 'ASC')
      .then (data) ->
        vm.Weeks = data
        return
    NFLAPI.GetTotalResultsBySeasonID(vm.season)
      .then (data) ->
        vm.Results = data
        return
    return

  # ----- #
  userWeekPts = (week, points) ->
    out = '0'
    out = pts.Points for pts in points when pts.WeekID is week
    out

  # ----- #
  init = () ->
    $scope.$parent.vm.Section = 'results'
    vm.season = 1
    NFLAPI.GetAllSeasons()
      .then (data) ->
        vm.Seasons = data
        return
    vm.user = $localStorage.user
    do changeSeason
    return

  vm.Seasons = []
  vm.Results = []
  vm.Weeks = []
  vm.prizePrefix = '$'
  vm.priceByPos = [
    { class: 'win', value: '7,400' }
    { class: 'lose', value: '250' } 
    { class: 'lose', value: '250' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '500' } 
    { class: 'lose', value: '600' } 
    { class: 'lose', value: '600' } 
    { class: 'lose', value: '600' } 
    { class: 'lose', value: '600' } 
  ]

  vm.changeSeason = changeSeason
  vm.userWeekPts = userWeekPts

  do init
  return


angular
.module('nfl')
.controller('globalResultsCtrl', globalResultsCtrl)