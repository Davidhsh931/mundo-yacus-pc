"""
ANÁLISIS ESTADÍSTICO - PRUEBA INDEPENDIENTE
Mundo Yacus - Simulación de datos cuantitativos

Este script es una prueba de conceptos estadísticos básicos
aplicados a datos simulados del sistema de gestión.
No está integrado al sistema principal.
"""

import pandas as pd
import numpy as np

# Configurar semilla para reproducibilidad
np.random.seed(42)

# Generar 150 registros simulados
# Datos realistas para el contexto de cuyes y ventas
data = {
    'average_weight': np.random.uniform(800, 1200, 150),  # Peso en gramos (800-1200g)
    'price': np.random.uniform(10, 100, 150),              # Precio en soles (10-100)
    'stock': np.random.randint(1, 51, 150),               # Stock disponible (1-50 unidades)
    'edad': np.random.randint(1, 13, 150)                 # Edad en meses (1-12 meses)
}

df = pd.DataFrame(data)

print("=" * 60)
print("ANÁLISIS ESTADÍSTICO - MUNDO YACUS (PRUEBA)")
print("=" * 60)

# Calcular y mostrar estadísticas descriptivas
print("\n--- ESTADÍSTICAS DESCRIPTIVAS ---\n")
for col in df.columns:
    print(f"COLUMNA: {col.upper()}")
    print(f"  Media:              {df[col].mean():.2f}")
    print(f"  Varianza:          {df[col].var():.2f}")
    print(f"  Desviación Estándar: {df[col].std():.2f}")
    print(f"  Mínimo:             {df[col].min():.2f}")
    print(f"  Máximo:             {df[col].max():.2f}")
    print()

# Función para detectar anomalías usando z-score
def detectar_anomalias(df):
    """
    Detecta anomalías basándose en el z-score.
    Un dato es anómalo si su z-score absoluto es mayor a 3.
    Z-score = (dato - media) / desviación_estándar
    """
    print("\n--- DETECCIÓN DE ANOMALÍAS (Z-SCORE > 3) ---\n")
    
    anomalias_encontradas = False
    
    for col in df.columns:
        mean = df[col].mean()
        std = df[col].std()
        
        for idx, value in df[col].items():
            z_score = (value - mean) / std
            
            if abs(z_score) > 3:
                anomalias_encontradas = True
                tipo_alerta = "SALUD" if col in ['average_weight', 'edad'] else "VENTA"
                print(f"⚠️  ALERTA DE {tipo_alerta}:")
                print(f"    Columna:     {col}")
                print(f"    Valor:       {value:.2f}")
                print(f"    Z-Score:     {z_score:.2f}")
                print(f"    Desviaciones: {abs(z_score):.2f}σ de la media")
                print()
    
    if not anomalias_encontradas:
        print("✓ No se detectaron anomalías en el dataset.")
        print("  Todos los datos están dentro de 3 desviaciones estándar.")

# Ejecutar detección de anomalías
detectar_anomalias(df)

print("\n" + "=" * 60)
print("FIN DEL ANÁLISIS")
print("=" * 60)
