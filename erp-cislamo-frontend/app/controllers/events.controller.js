angular.module('erpCislamoApp')
    .controller('EventsController', ['$scope', '$location', 'AuthService', 'EventService', function($scope, $location, AuthService, EventService) {
        $scope.events = [];
        $scope.loading = true;
        $scope.showForm = false;
        $scope.currentUser = AuthService.getUser();
        
        $scope.newEvent = {
            title: '',
            description: '',
            start_date: '',
            end_date: '',
            location: '',
            status: 'scheduled'
        };
        
        $scope.loadEvents = function() {
            EventService.getAll()
                .then(function(response) {
                    $scope.events = response.data;
                })
                .catch(function(error) {
                    console.error('Erro ao carregar eventos:', error);
                })
                .finally(function() {
                    $scope.loading = false;
                });
        };
        
        $scope.toggleForm = function() {
            $scope.showForm = !$scope.showForm;
            if (!$scope.showForm) {
                $scope.resetForm();
            }
        };
        
        $scope.createEvent = function() {
            if ($scope.newEvent.title && $scope.newEvent.start_date && $scope.newEvent.end_date) {
                EventService.create($scope.newEvent)
                    .then(function(response) {
                        $scope.events.unshift(response.data);
                        $scope.toggleForm();
                        alert('Evento criado com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao criar evento.');
                    });
            }
        };
        
        $scope.deleteEvent = function(eventId) {
            if (confirm('Tem certeza que deseja remover este evento?')) {
                EventService.delete(eventId)
                    .then(function() {
                        $scope.events = $scope.events.filter(function(e) { return e.id !== eventId; });
                        alert('Evento removido com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao remover evento.');
                    });
            }
        };
        
        $scope.resetForm = function() {
            $scope.newEvent = {
                title: '',
                description: '',
                start_date: '',
                end_date: '',
                location: '',
                status: 'scheduled'
            };
        };
        
        $scope.logout = function() {
            AuthService.logout().finally(function() {
                $location.path('/login');
            });
        };
        
        $scope.goTo = function(path) {
            $location.path(path);
        };
        
        $scope.loadEvents();
    }]);

