angular.module('erpCislamoApp')
    .controller('DashboardController', ['$scope', '$location', 'AuthService', 'ApiService', function($scope, $location, AuthService, ApiService) {
        $scope.user = AuthService.getUser();
        $scope.stats = {};
        $scope.loading = true;
        
        $scope.loadStats = function() {
            ApiService.get('/dashboard/stats')
                .then(function(response) {
                    $scope.stats = response.data;
                })
                .catch(function(error) {
                    console.error('Erro ao carregar estatísticas:', error);
                })
                .finally(function() {
                    $scope.loading = false;
                });
        };
        
        $scope.logout = function() {
            AuthService.logout().finally(function() {
                $location.path('/login');
            });
        };
        
        $scope.goTo = function(path) {
            $location.path(path);
        };
        
        $scope.loadStats();
    }]);

