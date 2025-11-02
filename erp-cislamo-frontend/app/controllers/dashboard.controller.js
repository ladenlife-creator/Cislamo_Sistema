angular.module('erpCislamoApp')
    .controller('DashboardController', ['$scope', '$location', 'AuthService', 'ApiService', function($scope, $location, AuthService, ApiService) {
        $scope.user = AuthService.getUser();
        $scope.stats = {};
        $scope.loading = true;
        
        $scope.loadStats = function() {
            $scope.loading = true;
            
            ApiService.get('/dashboard/stats')
                .then(function(response) {
                    console.log('Estatísticas do dashboard:', response.data);
                    
                    if (response && response.data) {
                        $scope.stats = response.data;
                        console.log('Utilizadores:', $scope.stats.users_count);
                        console.log('Documentos:', $scope.stats.documents_count);
                        console.log('Eventos:', $scope.stats.events_count);
                        console.log('Documentos recentes:', $scope.stats.recent_documents?.length || 0);
                        console.log('Próximos eventos:', $scope.stats.upcoming_events?.length || 0);
                    } else {
                        console.warn('Resposta inválida:', response);
                        $scope.stats = {};
                    }
                })
                .catch(function(error) {
                    console.error('Erro ao carregar estatísticas:', error);
                    if (error.data) {
                        console.error('Dados do erro:', error.data);
                    }
                    $scope.stats = {};
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

