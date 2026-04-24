# Aprendizaje Supervisado en Mundo Yacus

## Principios Estadísticos

### Variables para IA en el Sistema

Para que el sistema de gestión de Mundo Yacus sea considerado un software con IA, debe analizar los siguientes tipos de variables:

**Variables Cualitativas:**
- **Raza/Breed:** Andina, Merino, Perú, Americano
- **Categoría:** Animal, Carne, Otros
- **Estado del producto:** Disponible, Agotado, Reservado
- **Comunidad/Vendedor:** Comunidad Yacus, Comunidad San Cristóbal, etc.

**Variables Cuantitativas:**
- **Peso promedio (average_weight):** 800-1200 gramos
- **Precio (price):** 10-100 soles
- **Stock (stock):** 1-50 unidades
- **Edad del animal:** 1-12 meses
- **Tiempo hasta venta:** Días desde publicación hasta venta
- **Número de ventas por período:** Ventas diarias/semanales/mensuales

---

## Varianza y Desviación Estándar

### Detección de Anomalías en Crecimiento de Animales

El cálculo de la dispersión de datos ayuda a detectar anomalías de la siguiente manera:

**Ejemplo en peso de cuyes:**
- Si el peso promedio es 950g con una desviación estándar de 50g
- Un animal con peso de 600g está a más de 7 desviaciones estándar de la media
- Esto indica una anomalía que puede ser causada por:
  - Problemas de salud o nutrición
  - Error en la medición
  - Animal enfermo o en desarrollo anormal

**Aplicación práctica:**
- Alertas automáticas cuando un animal está fuera del rango normal
- Identificación de problemas de alimentación en la comunidad
- Detección de errores en el registro de datos

### Detección de Anomalías en Rendimiento de Ventas

**Ejemplo en ventas diarias:**
- Si las ventas diarias promedio son 10 unidades con desviación de 3
- Un día con 0 ventas o 30 ventas es anómalo
- Esto puede indicar:
  - Fallo técnico en el sistema
  - Promoción exitosa (venta alta)
  - Fraude o error de registro (venta anormalmente alta)
  - Problema de stock o demanda (venta anormalmente baja)

**Aplicación práctica:**
- Monitoreo de salud del sistema
- Detección de oportunidades de marketing
- Identificación de problemas operativos

---

## Aprendizaje Supervisado

### Flujo de Trabajo Genérico (9 Etapas)

1. **Recolección de Datos:**
   - Capturar imágenes de cuyes con cámara
   - Registrar datos asociados (peso, edad, raza)
   - Almacenar en dataset organizado

2. **Etiquetado Manual:**
   - Asignar etiquetas a cada imagen (raza, categoría)
   - Crear archivos de ground truth
   - Verificar calidad de etiquetas

3. **Preprocesamiento:**
   - Redimensionar imágenes a tamaño uniforme
   - Normalizar valores de píxeles (0-1)
   - Aplicar data augmentation (rotación, zoom, flip)
   - Balancear clases si hay desequilibrio

4. **División del Dataset:**
   - 70% para entrenamiento
   - 20% para validación
   - 10% para prueba
   - Asegurar distribución similar en cada split

5. **Entrenamiento:**
   - El modelo aprende patrones visuales
   - Mapea características → etiquetas
   - Ajusta pesos mediante backpropagation
   - Minimiza función de pérdida

6. **Validación:**
   - Evaluar precisión en datos no vistos
   - Calcular métricas (accuracy, precision, recall)
   - Detectar overfitting/underfitting
   - Comparar con baseline

7. **Ajuste:**
   - Modificar hiperparámetros si validación insuficiente
   - Ajustar learning rate, batch size, arquitectura
   - Reentrenar con parámetros optimizados
   - Iterar hasta satisfacer métricas

8. **Despliegue:**
   - Integrar modelo en API REST
   - Desplegar en servidor de producción
   - Configurar endpoint para predicciones
   - Documentar uso del servicio

9. **Monitoreo:**
   - Evaluar rendimiento en producción
   - Recopilar feedback de predicciones
   - Detectar drift de datos
   - Reentrenar periódicamente con nuevos datos

---

## Análisis de train_cuy.py

El script `train_cuy.py` implementa el flujo de aprendizaje supervisado para clasificación de cuyes:

### Uso de cuy_data.yaml

El archivo `cuy_data.yaml` especifica:
- **Rutas de imágenes:** Directorios de entrenamiento y validación
- **Número de clases:** Cantidad de razas de cuy a clasificar
- **Nombres de clases:** Etiquetas ground truth (Andina, Merino, Perú, etc.)

### Proceso de Entrenamiento

1. **Carga de datos:** YOLO lee las imágenes y sus etiquetas desde las rutas especificadas
2. **Ground truth:** Cada imagen viene con su etiqueta correcta (raza) en formato YOLO
3. **Backpropagation:** Durante el entrenamiento, el modelo:
   - Extrae características visuales de cada imagen
   - Compara predicción con etiqueta real
   - Calcula error de clasificación
   - Ajusta pesos para minimizar error
4. **Validación:** En cada epoch, evalúa precisión en dataset de validación
5. **Guardado:** Al finalizar, guarda el modelo entrenado en archivo `.pt`

---

## Justificación Técnica: ¿Por qué es Aprendizaje Supervisado?

El sistema de clasificación de cuyes es **Aprendizaje Supervisado** porque:

### 1. Datos con Etiquetas Conocidas
- El modelo recibe imágenes con etiquetas predefinidas (razas)
- Las etiquetas son ground truth proporcionadas por expertos humanos
- No hay descubrimiento automático de categorías

### 2. Mapeo Explícito
- El objetivo es aprender el mapeo: características visuales → etiquetas
- El modelo aprende patrones que distinguen cada raza
- El output debe coincidir con las etiquetas de entrenamiento

### 3. Ground Truth en Entrenamiento
- Durante backpropagation, se usa la etiqueta real para calcular pérdida
- El error se mide contra el ground truth, no contra datos no etiquetados
- La optimización busca minimizar diferencia con etiquetas conocidas

### 4. Validación Supervisada
- La precisión se mide comparando predicciones con etiquetas reales
- No hay evaluación basada en estructura de datos no etiquetados
- El éxito se define por capacidad de replicar etiquetas manuales

---

## Propósito del Sistema

### Objetivo Final

El sistema de aprendizaje supervisado en Mundo Yacus tiene como propósito:

**Automatizar la clasificación de cuyes en imágenes nuevas:**
- El usuario sube una foto de un cuy
- El modelo predice automáticamente su raza/categoría
- El sistema replica el etiquetado manual que se usó durante entrenamiento
- Se reduce el tiempo y esfuerzo de clasificación manual

**Beneficios:**
- Clasificación consistente y objetiva
- Escalabilidad a grandes volúmenes de imágenes
- Integración con el sistema de gestión existente
- Mejora continua con reentrenamiento periódico

---

## Conclusión

El sistema de Mundo Yacus implementa aprendizaje supervisado para clasificación visual de cuyes, utilizando principios estadísticos para detección de anomalías y seguimiento de un flujo de trabajo estándar de machine learning desde recolección hasta monitoreo en producción.
