matchsCtrl = ($scope, NFLAPI, $localStorage, $state, $stateParams, $uibModal, $filter) ->
  vm = @


  # ---- #
  init = () ->
    vm.weekID = $stateParams.weekID
    vm.week = $stateParams.week
    NFLAPI.GetAllTeams()
      .then (data) ->
        vm.Teams = data
        return
    NFLAPI.GetMatchsByWeekID(vm.weekID)
      .then (data) ->
        vm.Matchs = data
        return
    return


  # ----- #
  saveNewMatch = () ->
    if vm.HomeTeamNew is '0' or vm.AwayTeamNew is '0' or vm.NewMatchStart is ''
      alert 'Todos los datos son requeridos'
      return false
    obj = 
      Home: vm.HomeTeamNew
      Away: vm.AwayTeamNew
      Week: vm.weekID
      Time: moment(vm.NewMatchStart).format('YYYY-MM-DD HH:mm:ss')
      Limit: moment(vm.NewMatchLimit).format('YYYY-MM-DD HH:mm:ss')
    NFLAPI.SaveNewMatch(obj)
      .then (data) ->
        vm.HomeTeamNew = '0'
        vm.AwayTeamNew = '0'
        vm.NewMatchStart = ''
        vm.NewMatchLimit = ''
        angular.element '#homeSelected'
          .find 'img'
          .attr 'src', 'img/select.jpg'
        angular.element '#awaySelected'
          .find 'img'
          .attr 'src', 'img/select.jpg'
        vm.Matchs.push data.newMatch[0]
        return
    return

  # ----- #
  deteleMatch = () ->
    return


  # ----- #
  selectTeam = (cur) ->
    elmID = '#' + cur
    current = angular.element elmID
    modalInstance = $uibModal.open
      templateUrl: 'partials/selectTeam.html'
      controller: 'selectTeamCtrl'
      controllerAs: 'vm'
      size: 'md'
      resolve:
        items: () ->
          vm.Teams
    modalInstance.result
      .then (selectedItem) ->
        if cur is 'homeSelected'     
          vm.HomeTeamNew = if selectedItem isnt vm.AwayTeamNew then selectedItem else vm.HomeTeamNew
          selectedItem = vm.HomeTeamNew
        else if cur is 'awaySelected'
          vm.AwayTeamNew = if selectedItem isnt vm.HomeTeamNew then selectedItem else vm.AwayTeamNew
          selectedItem = vm.AwayTeamNew        
        if selectedItem isnt '0'
          selectedTeam = $filter('filter')(vm.Teams, { TeamId: selectedItem }, true)
          current
            .find 'img'
            .attr 'src', 'img/'+selectedTeam[0].Image
        return
    return

  vm.dateTimePickerOption = 
    icons:
      next:'glyphicon glyphicon-arrow-right'
      previous:'glyphicon glyphicon-arrow-left'
      up:'glyphicon glyphicon-arrow-up'
      down:'glyphicon glyphicon-arrow-down'

  vm.Teams = []
  vm.Matchs = []

  vm.HomeTeamNew = '0'
  vm.AwayTeamNew = '0'

  vm.deteleMatch = deteleMatch
  vm.selectTeam = selectTeam
  vm.saveNewMatch = saveNewMatch

  do init

  return


angular
  .module('nfl')
  .controller('matchsCtrl', matchsCtrl)