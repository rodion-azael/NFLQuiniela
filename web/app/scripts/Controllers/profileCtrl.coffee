profileCtrl = ($scope, NFLAPI, $localStorage, $state) ->
  vm = @

  # ----- #
  init = () ->
    $scope.$parent.vm.Section = 'profile'
    vm.passMissMatch = false
    return

  # ----- #
  matchPass = () ->
    if vm.newPass isnt vm.repPass
      vm.passMissMatch = true
    else
      vm.passMissMatch = false
    return

  # ----- #
  updatePassWord = () ->
    if vm.passMissMatch or vm.current is ''
      alert 'Valide que los campos sean correctos'
      return false
    obj = 
      cur: vm.current
      new: vm.newPass
    NFLAPI.SaveNewPass(obj)
      .then (data) ->
        console.log data
        return
    return


  vm.matchPass = matchPass
  vm.updatePassWord = updatePassWord

  vm.current = ''

  do init
  return


angular
	.module('nfl')
	.controller('profileCtrl', profileCtrl)