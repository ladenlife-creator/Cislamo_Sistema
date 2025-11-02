angular.module('erpCislamoApp')
    .controller('LoginController', ['$scope', '$location', 'AuthService', function($scope, $location, AuthService) {
        $scope.credentials = {
            email: '',
            password: ''
        };
        
        $scope.error = null;
        $scope.loading = false;
        $scope.showPassword = false;
        
        $scope.togglePassword = function() {
            $scope.showPassword = !$scope.showPassword;
        };
        
        $scope.login = function() {
            $scope.error = null;
            $scope.loading = true;
            
            AuthService.login($scope.credentials)
                .then(function(response) {
                    $location.path('/dashboard');
                })
                .catch(function(error) {
                    $scope.error = 'Credenciais inválidas. Por favor, tente novamente.';
                })
                .finally(function() {
                    $scope.loading = false;
                });
        };
    }]);

