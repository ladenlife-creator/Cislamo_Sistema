angular.module('erpCislamoApp')
    .controller('UsersController', ['$scope', '$location', 'AuthService', 'UserService', function($scope, $location, AuthService, UserService) {
        $scope.users = [];
        $scope.allUsers = []; // Todos os usuários (sem filtro)
        $scope.loading = true;
        $scope.showForm = false;
        $scope.showFilters = false; // Controla visibilidade dos filtros
        $scope.currentUser = AuthService.getUser();
        
        // Filtros
        $scope.filters = {
            search: '',
            dateFrom: '',
            dateTo: ''
        };
        
        $scope.newUser = {
            name: '',
            email: '',
            password: ''
        };
        
        $scope.loadUsers = function() {
            $scope.loading = true;
            UserService.getAll()
                .then(function(response) {
                    // Garantir que sempre seja um array
                    if (Array.isArray(response.data)) {
                        $scope.allUsers = response.data;
                        $scope.applyFilters(); // Aplicar filtros após carregar
                    } else {
                        $scope.allUsers = [];
                        $scope.users = [];
                    }
                })
                .catch(function(error) {
                    console.error('Erro ao carregar utilizadores:', error);
                    $scope.allUsers = [];
                    $scope.users = [];
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
        
        $scope.toggleFilters = function() {
            $scope.showFilters = !$scope.showFilters;
        };
        
        $scope.createUser = function() {
            if ($scope.newUser.name && $scope.newUser.email && $scope.newUser.password) {
                UserService.create($scope.newUser)
                    .then(function(response) {
                        $scope.allUsers.unshift(response.data);
                        $scope.applyFilters(); // Reaplicar filtros após criar
                        $scope.toggleForm();
                        alert('Utilizador criado com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao criar utilizador.');
                    });
            }
        };
        
        // Função para aplicar filtros
        $scope.applyFilters = function() {
            var filtered = $scope.allUsers.filter(function(user) {
                // Filtro de busca (nome ou email)
                if ($scope.filters.search) {
                    var search = $scope.filters.search.toLowerCase();
                    var nameMatch = user.name && user.name.toLowerCase().indexOf(search) !== -1;
                    var emailMatch = (user.email && user.email.toLowerCase().indexOf(search) !== -1) ||
                                    (user.identifier && user.identifier.toLowerCase().indexOf(search) !== -1);
                    if (!nameMatch && !emailMatch) {
                        return false;
                    }
                }
                
                // Filtro por data de criação (a partir de)
                if ($scope.filters.dateFrom && user.created_at) {
                    var dateFrom = new Date($scope.filters.dateFrom);
                    var userDate = new Date(user.created_at);
                    if (userDate < dateFrom) {
                        return false;
                    }
                }
                
                // Filtro por data de criação (até)
                if ($scope.filters.dateTo && user.created_at) {
                    var dateTo = new Date($scope.filters.dateTo);
                    dateTo.setHours(23, 59, 59); // Incluir o dia inteiro
                    var userDate = new Date(user.created_at);
                    if (userDate > dateTo) {
                        return false;
                    }
                }
                
                return true;
            });
            
            $scope.users = filtered;
        };
        
        // Função para limpar filtros
        $scope.clearFilters = function() {
            $scope.filters = {
                search: '',
                dateFrom: '',
                dateTo: ''
            };
            $scope.applyFilters();
        };
        
        $scope.deleteUser = function(userId) {
            if (confirm('Tem certeza que deseja remover este utilizador?')) {
                UserService.delete(userId)
                    .then(function() {
                        $scope.allUsers = $scope.allUsers.filter(function(u) { return u.id !== userId; });
                        $scope.applyFilters(); // Reaplicar filtros após remover
                        alert('Utilizador removido com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao remover utilizador.');
                    });
            }
        };
        
        $scope.resetForm = function() {
            $scope.newUser = {
                name: '',
                email: '',
                password: ''
            };
        };
        
        $scope.goTo = function(path) {
            $location.path(path);
        };
        
        $scope.logout = function() {
            AuthService.logout().finally(function() {
                $location.path('/login');
            });
        };
        
        $scope.loadUsers();
    }]);

