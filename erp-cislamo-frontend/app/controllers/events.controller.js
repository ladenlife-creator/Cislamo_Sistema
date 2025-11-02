angular.module('erpCislamoApp')
    .controller('EventsController', ['$scope', '$location', 'AuthService', 'EventService', function($scope, $location, AuthService, EventService) {
        $scope.events = [];
        $scope.allEvents = []; // Todos os eventos (sem filtro)
        $scope.loading = true;
        $scope.showForm = false;
        $scope.showFilters = false; // Controla visibilidade dos filtros
        $scope.currentUser = AuthService.getUser();
        
        // Filtros
        $scope.filters = {
            search: '',
            status: '',
            location: '',
            dateFrom: '',
            dateTo: ''
        };
        
        $scope.statusOptions = [
            { value: '', label: 'Todos os Status' },
            { value: 'scheduled', label: 'Agendado' },
            { value: 'ongoing', label: 'Em andamento' },
            { value: 'completed', label: 'Concluído' },
            { value: 'cancelled', label: 'Cancelado' }
        ];
        
        $scope.newEvent = {
            title: '',
            description: '',
            start_date: '',
            end_date: '',
            location: '',
            status: 'scheduled'
        };
        
        $scope.loadEvents = function() {
            $scope.loading = true;
            $scope.events = [];
            
            EventService.getAll()
                .then(function(response) {
                    console.log('Resposta completa:', response);
                    console.log('response.data tipo:', typeof response.data);
                    console.log('response.data é array?', Array.isArray(response.data));
                    
                    // O AngularJS $http sempre retorna response.data
                    if (response && response.data) {
                        if (Array.isArray(response.data)) {
                            $scope.allEvents = response.data;
                            $scope.applyFilters(); // Aplicar filtros após carregar
                            console.log('Eventos atribuídos:', $scope.allEvents.length);
                        } else {
                            console.warn('response.data não é um array:', response.data);
                            $scope.allEvents = [];
                            $scope.events = [];
                        }
                    } else {
                        console.warn('Resposta inválida:', response);
                        $scope.allEvents = [];
                        $scope.events = [];
                    }
                })
                .catch(function(error) {
                    console.error('Erro ao carregar eventos:', error);
                    if (error.data) {
                        console.error('Dados do erro:', error.data);
                    }
                    if (error.status) {
                        console.error('Status HTTP:', error.status);
                    }
                    $scope.allEvents = [];
                    $scope.events = [];
                })
                .finally(function() {
                    $scope.loading = false;
                    console.log('Loading finalizado. Total eventos:', $scope.events.length);
                });
        };
        
        $scope.toggleForm = function() {
            $scope.showForm = !$scope.showForm;
            if (!$scope.showForm) {
                $scope.resetForm();
            }
        };
        
        $scope.toggleFilters = function() {
            $scope.showFilters = !$scope.showFilters;
        };
        
        $scope.createEvent = function() {
            if ($scope.newEvent.title && $scope.newEvent.start_date && $scope.newEvent.end_date) {
                EventService.create($scope.newEvent)
                    .then(function(response) {
                        $scope.allEvents.unshift(response.data);
                        $scope.applyFilters(); // Reaplicar filtros após criar
                        $scope.toggleForm();
                        alert('Evento criado com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao criar evento.');
                    });
            }
        };
        
        // Função para aplicar filtros
        $scope.applyFilters = function() {
            var filtered = $scope.allEvents.filter(function(event) {
                // Filtro de busca (título ou descrição)
                if ($scope.filters.search) {
                    var search = $scope.filters.search.toLowerCase();
                    var titleMatch = event.title && event.title.toLowerCase().indexOf(search) !== -1;
                    var descMatch = event.description && event.description.toLowerCase().indexOf(search) !== -1;
                    if (!titleMatch && !descMatch) {
                        return false;
                    }
                }
                
                // Filtro por status
                if ($scope.filters.status && event.status !== $scope.filters.status) {
                    return false;
                }
                
                // Filtro por localização
                if ($scope.filters.location) {
                    var location = $scope.filters.location.toLowerCase();
                    if (!event.location || event.location.toLowerCase().indexOf(location) === -1) {
                        return false;
                    }
                }
                
                // Filtro por data de início
                if ($scope.filters.dateFrom) {
                    var dateFrom = new Date($scope.filters.dateFrom);
                    var eventStartDate = new Date(event.start_date);
                    if (eventStartDate < dateFrom) {
                        return false;
                    }
                }
                
                // Filtro por data de fim
                if ($scope.filters.dateTo) {
                    var dateTo = new Date($scope.filters.dateTo);
                    dateTo.setHours(23, 59, 59); // Incluir o dia inteiro
                    var eventStartDate = new Date(event.start_date);
                    if (eventStartDate > dateTo) {
                        return false;
                    }
                }
                
                return true;
            });
            
            $scope.events = filtered;
        };
        
        // Função para limpar filtros
        $scope.clearFilters = function() {
            $scope.filters = {
                search: '',
                status: '',
                location: '',
                dateFrom: '',
                dateTo: ''
            };
            $scope.applyFilters();
        };
        
        $scope.deleteEvent = function(eventId) {
            if (confirm('Tem certeza que deseja remover este evento?')) {
                EventService.delete(eventId)
                    .then(function() {
                        $scope.allEvents = $scope.allEvents.filter(function(e) { return e.id !== eventId; });
                        $scope.applyFilters(); // Reaplicar filtros após remover
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

