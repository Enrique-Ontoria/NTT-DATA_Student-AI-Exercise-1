<?php

namespace Drupal\pokedex\Service;

// Llamada a la API
use GuzzleHttp\ClientInterface;

// Se utilizará para capturar errores en solicitud de API
use GuzzleHttp\Exception\RequestException;

class PokedexService {

    // Cliente HTTP que pedirá datos a la pokeAPI
    protected $httpClient;


    /**
     * 1º CONSTRUCTOR
     * Psamos el servicio http_client a la variable previamente
     * definida --> $httpClient
     */

    public function __construct(ClientInterface $http_client) {
        $this->httpClient = $http_client;
    }

    /**
     * Metodo para obtener listado de todos los pokemons.
     * 
     * $limit -> Indicamos el numero maximo de pokemon por lista
     * $offset -> Indicamos como va a ir saltando de x cantidad de pokemons
     */
    public function getList($limit = 20, $offset = 0){
        try {
            /**
             * Hacemos la primera llamada al endpoint que ajusta los parametros de paginación
             * Los cuales serán los que nosotros hemos obtenido através de nuestra función
             * previamente calculados en el Controller.
             */
            $url = "https://pokeapi.co/api/v2/pokemon?limit=$limit&offset=$offset";
            //$url = "https://pokeapi.com/apis/v233/pokemon?limit=$limit&offset=$offset"; // <-- ESTADO: reintentar
            $solicitud = $this->httpClient->request('GET', $url, [
                'query' => [
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ]);
            
            $datos = json_decode($solicitud->getBody(), TRUE);

            $lista = []; // Array que almacenará cada pokemon obtenido
            foreach($datos['results'] as $pokemon){
                $url = explode('/', rtrim($pokemon['url'], '/'));
                $id = end($url);
                $item = [
                    'id' => $id,
                    'name' => $pokemon['name'],
                    'image' => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/$id.png", // Repositorio de imagenes de pokemons segun ID.
                ];

                $lista[] = $item;
            }

            return [
                'pokemons' => $lista,
                /**
                 * El parametro 'next' de la pokeAPI nos ayuda para la paginación 
                 * ya que nos indica si existe una pagina siguiente.
                 */
                'next_page' => !empty($datos['next']),
            ];

        } catch (\Exception $e) {
            return 'Error';
        }
    }

    /**
     * Metodo para obtener detalles de un solo pokemon
     * segun el nombre obtenido atraves del Path previamente
     * definido en el routing.yml
     */
    public function getPokemon($name){

        try {
            /**
             * Realizamos la peticion con nuestro httpClient indicando
             * el nombre que nos llega por el parametro de la función
             */
            $url = "https://pokeapi.co/api/v2/pokemon/$name";
            $solicitud  = $this->httpClient->request('GET', $url);
            $datos = json_decode($solicitud->getBody(), TRUE);

            //Nombre
            $pokemon['name'] = $datos['name'];

            //ID
            $pokemon['id'] = $datos['id'];

            //Imagen
            $pokemon['image'] = $datos['sprites']['front_default'];

            //Tipo(s)
            foreach ($datos['types'] as $type) {
                $pokemon['types'] = [
                    'name' => $type['type']['name']
                ];
            }

            //Altura
            $pokemon['height'] = $datos['height'];

            //Peso
            $pokemon['weight'] = $datos['weight'];

            //Evoluciones (obtenidas en una función aparte para dividir codigo)
            $pokemon['evo'] = $this->evolutionChain($datos['species']['url']);

            return $pokemon;
        }
        catch (\Exception $e){
            return NULL;
        }
    }

    /**
     * Función mejorable.
     * 
     * Proceso para obtener evoluciones:
     *  - 1º Paso -> Realizamos una petición a la 'url' de la especie
     *    previamente indicada en el parametro de la función
     * 
     *  - 2º Paso -> Una ves obtenida la especie, obtendremos la url
     *    correspondiente a los datos de evolución de dicha especie
     * 
     *  - 3º Paso -> Procedemos a recorrer mediante bucles foreach
     *    la estructura de evoluciones del pokemon comprobando previamente
     *    si existe dicha evolución.
     */
    private function evolutionChain($url){
        try{
            // 1º Paso
            $solicitud  = $this->httpClient->request('GET', $url);
            $datos = json_decode($solicitud->getBody(), TRUE);

            // 2º Paso
            $url_evoChain = $datos['evolution_chain']['url'];
            $solicitud  = $this->httpClient->request('GET', $url_evoChain);
            $datos = json_decode($solicitud->getBody(), TRUE);

            $datos = $datos['chain'];

            //3º Paso
            $url = $datos['species']['url']; // Forma base
            $id = basename(rtrim($url, '/'));
            $img = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png";
            $evos[] = [
                'name' => $datos['species']['name'],
                'image' => $img,
            ];
            //Comprobamos si tiene evolución.
            if(!empty($datos['evolves_to'])){ // 1º Evolución
                foreach ($datos['evolves_to'] as $evo){
                    $url = $evo['species']['url'];
                    $id = basename(rtrim($url, '/'));
                    $img = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png";
                    $evos[] = [
                        'name' => $evo['species']['name'],
                        'image' => $img,
                    ];
                    //Comprobamos si tiene otra evolucion más.
                    if(!empty($evo['evolves_to'])){ // 2º Evolución
                        foreach($evo['evolves_to'] as $lastEvo){
                            $url = $lastEvo['species']['url'];
                            $id = basename(rtrim($url, '/'));
                            $img = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png";
                            $evos[] = [
                                'name' => $lastEvo['species']['name'],
                                'image' => $img,
                            ];
                        }
                    }
                }
            }
            return $evos;
        }
        catch (\Exception $e){
            return NULL;
        }
    }


    
}