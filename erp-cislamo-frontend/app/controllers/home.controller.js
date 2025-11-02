angular.module('erpCislamoApp')
    .controller('HomeController', ['$scope', '$location', function($scope, $location) {
        $scope.features = [
            {
                icon: '👥',
                title: 'Gestão de Utilizadores',
                description: 'Controle completo sobre usuários e permissões'
            },
            {
                icon: '📄',
                title: 'Gestão de Documentos',
                description: 'Organize e gerencie documentos corporativos'
            },
            {
                icon: '📅',
                title: 'Gestão de Eventos',
                description: 'Crie e administre eventos organizacionais'
            },
            {
                icon: '📊',
                title: 'Relatórios Detalhados',
                description: 'Análises e insights para tomada de decisão'
            }
        ];
        
        $scope.goToLogin = function() {
            $location.path('/login');
        };
    }]);

