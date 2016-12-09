
nflMainCtrl = ($scope, $localStorage, $state) ->
  vm = @

  LogOut = () ->
    $localStorage.$reset()
    $state.go('login');
    return

  vm.user = $localStorage.user

  vm.Section = 'weeks'

  vm.LogOut = LogOut
  return


angular
  .module('nfl')
  .controller('nflMainCtrl', nflMainCtrl)
