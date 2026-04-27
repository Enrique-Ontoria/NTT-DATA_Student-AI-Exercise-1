<?php

// Dirección interna del modulo
namespace Drupal\pokedex\Controller;

//Clases base para la pokedex
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
//Servicio personalizado
use Drupal\pokedex\Service\PokedexService; // <- Servicio personalizado

use Drupal\Core\Url;

// Clase principal del controlador
class PokedexController extends ControllerBase {

    // Cliente HTTP que pedirá datos a la pokeAPI
    protected $pokedexService;

    // Contenedor de servicios de Drupal.
    public static function create(ContainerInterface $container) {
        return new static($container->get('pokedex.service'));
    }

    // Constructor para contectar con nuestro servicio que contiene la logica
    public function __construct(PokedexService $pokedex_service) {
        $this->pokedexService = $pokedex_service;
    }

    /**
     * Con Request podemos obtener información referente a los datos que nos llegan
     * de la URL de .../pokedex? en este casó buscaremos el valor de 'page' que se indica
     * en los enlaces <a> de pokedex.html.twig para así realizar el calculo del offset
     * y asi mostrar los pokemons de 20 en 20.
     */
    public function index(Request $request){
        $limit = 20;
        $page = (int)$request->query->get('page', 0);
        $offset = $page * $limit;

        // Tras realziar el calculo llamamos a la función de nuestro service
        $datos = $this->pokedexService->getList($limit, $offset);

        //$datos = []; // <-- ESTADO: Vacio
        /**
         * Si el catch de nuestro servicio lanza un error, mostraremos un enlace
         * que vuelve a redirigir al path de nuestro routing.yml para reintentar 
         * la llamada a la pokeAPI
         */
        if($datos === 'Error'){
            return [
                '#markup' => '<div class="error">Hubo un fallo de conexión...<a href="/pokedex" class="button">Reintentar</a></div>',
            ];
        }

        //Si no nos llegan datos del service lo indicamos por pantalla
        if (empty($datos)) {
            return ['#markup' => '<p> No se han encontrado resultados </p>']; 
        }

        return [
            // Datos Principales
            '#theme' => 'pokedex',
            '#pokemons' => $datos['pokemons'] ?? [],
            '#page' => $page,
            '#next_page' => $datos['next_page'],
            // Limpiar caché de datos de pagina actual
            '#cache' => [
                'contexts' => ['url.query_args:page'],
            ],
            // CSS
            '#attached' => [
                'library' => [
                    'pokedex/pokedex-list',
                    'pokedex/pokedex-list-script',
                ],
            ],
        ];
    }

    public function details($name){

        $datos = $this->pokedexService->getPokemon($name);

        if($datos === 'error'){
            return [
                '#markup' => '<div class="error">Hubo un fallo de conexión...<a href="/pokedex" class="button">Reintentar</a></div>',
            ];
        }

        if (empty($datos)) {
            return ['#markup' => '<p> No se han encontrado resultados </p>']; 
        }

        return [
            // Datos Principales
            '#theme' => 'pokemon',
            '#pokemon' => $datos ?? [],
            //CSS
            '#attached' => [
                'library' => [
                    'pokedex/pokedex-details',
                    'pokedex/pokedex-evo',
                ],
            ],
        ];
    }
}