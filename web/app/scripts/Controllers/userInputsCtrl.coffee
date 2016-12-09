userInputsCtrl = ($scope, NFLAPI, $localStorage, $state, $stateParams) ->
  vm = @
  
  # ----- #
  init = () ->
    vm.weekID = $stateParams.weekID
    vm.week = $stateParams.week
    NFLAPI.GetUserInputsByWeekId(vm.weekID)
      .then (data) ->
        if data.relogin
          $localStorage.$reset()
          $state.go 'login'
        vm.Matchs = data
        for match in vm.Matchs when match.UserInput isnt '0'
          vm.Inputs[match.MatchID] = 
            team: match.UserInput
            high: match.High
        console.log vm.Inputs
        return
    return

  # ----- #
  saveInputs = () ->
    NFLAPI.SaveInputs(vm.weekID, vm.Inputs)
      .then (data) ->
        rem = vm.Matchs.length - data.saved
        if data.success
          alert 'Has pronosticado ' + data.saved + ' partidos. Te restan ' + rem + ' por pronosticar.'
          $state.go 'main.viewResults', {weekID: vm.weekID, week:vm.week}
        else
          alert 'Ocurrio un error, intenta de nuevo.'
        return
    return

  # ----- #
  isEditable = (id) ->
    match = vm.Matchs[id]
    if match.IsEditable is '0' then return false
    if moment(new Date()).isBefore(match.CaptureLimit) then return true else return false

  # ----- #
  pickTeam = (match, team, high, index) ->
    if !isEditable index
      alert 'Este juego ya no es editable.'
      return false
    elm = '#team'+team
    $(elm).closest 'tr'
      .find '.team-selected'
      .removeClass 'team-selected'
    $(elm).addClass 'team-selected'
    vm.Inputs[match.toString()] = 
      team: if team isnt 0 then team else vm.Inputs[match.toString()]['team']
      high: high
    return

  vm.Matchs = []
  vm.Inputs = {}

  vm.pickTeam = pickTeam
  vm.saveInputs = saveInputs
  vm.isEditable = isEditable

  do init
	
  return

angular
	.module('nfl')
	.controller('userInputsCtrl', userInputsCtrl)