DESCRIPCIÓN
-----------------------------------------------------------------------------
=============================================================================
-----------------------------------------------------------------------------

Creación de un módulo personalizado en Drupal para consumir la pokeAPI, concretamente
el endpoint: https://pokeapi.co/api/v2/pokemon?limit=100000&offset=0 y asi obtener
un listado de pokemons con su información principal, por otra parte se ha implementado
la llamada a otro endpoint: https://pokeapi.co/api/v2/pokemon/{pokemon_name} para obtener
detalles especificos de un pokemon en concreto.

Como extra añadido, se han consultado los endpoints de:
    - https://pokeapi.co/api/v2/pokemon-species/{id_de_especie}/
    - https://pokeapi.co/api/v2/evolution-chain/{id_de_evolucion}/

Para obtener las evolciones correspondientes de cada pokemon.

SETUP
-----------------------------------------------------------------------------
=============================================================================
-----------------------------------------------------------------------------

DRUPAL

    El desarrollo de este modulo persoliazado en Drupal se ha realizado mediante el
    despliegue de un proyecto en drupal con las siguientes especificaciones:
        - Drupal 11.3.8 
        - PHP 8.3.0
        - Drush 13.7.2.0
        - Implementado con DDEV. 


INSTALACIÓN

    1º PASO
    Una vez configurado el entorno de drupal, deberás de crear un nuevo modulo personalizado
    en la siguiente ruta: web/modules/custom/pokedex

    2º PASO
    El siguiente paso sera instalar el nuevo modulo persoliazado introduciendo unicamente estos
    dos comandos que se muestran a continuación
        - drush en pokedex
        - drush cr

DECISIONES TÉCNICAS
-----------------------------------------------------------------------------
=============================================================================
-----------------------------------------------------------------------------

1.- Logica de consumo de pokeAPI desacoplada del controlador.

    En el desarrollo de este modulo se ha optado por separar la logica encargada
    de gestionar la obtención de datos de la pokeAPI en un servicio (PokedexService),
    consiguiendo asi un mejor entendimiento de como funciona el flujo a la hora de
    obtener los datos de cada pokemon.

2.- Gestión de paginación (Offset/Limit)

    Se ha implementado un sencillo calculo manual de offset principalmente basado en un
    parametro ?page que se establece en pokedex.html.twig mediante enlaces <a> donde se 
    indica el numero de 'page' en el que se encuentra el usuario actualmente
    
        Motivo: Esta implementación proporciona mantener las URL's del modulo persoliazado
        limpias de cara al usuario de manera totalmente independiente a como trabajan las 
        peticiones de los endpoint de la pokeAPI

3.- Limpieza de caché en cada interacción referente a la paginación.

    Se ha implementado Cache Context (url.query_args:page) en el Render Array del controller

    Motivo: Es necesaria esta implementación para evitar que Drupal muestre siempre la misma
    pagina y asi aseguramos que el sistema de 'pages' implementado siemre sea fiel a la navegación
    que realize el usuario

4.- Limpieza de JSON devueltos al .html.twig

    Cuando se obtiene el JSON de manera inicial ya sea del listado de pokemons o de los detalles
    de un pokemon en concreto, se procede a filtrar a una variable en especifico unicamente los 
    datos solicitados en el ejercicio, asi en el ultimo proceso de envio de variables al Twig, nos 
    aseguramos de mandar unicamente la información solitada, evitando asi proporcionar una gran 
    cantidad de datos innecesarios.



