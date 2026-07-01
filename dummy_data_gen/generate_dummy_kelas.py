import pandas as pd
import os

data = {
    'Tingkatan': [],
    'Kelas': [],
    'Rombel': []
}

# Ibtida'iyah (1-3)
for cls in [1, 2, 3]:
    for rombel in ['A', 'B']:
        data['Tingkatan'].append("Ibtida'iyah")
        data['Kelas'].append(cls)
        data['Rombel'].append(rombel)

# Tsanawiyah (7-9)
for cls in [7, 8, 9]:
    for rombel in ['A', 'B']:
        data['Tingkatan'].append("Tsanawiyah")
        data['Kelas'].append(cls)
        data['Rombel'].append(rombel)

# Aliyah (10-12)
for cls in [10, 11, 12]:
    for rombel in ['A', 'B']:
        data['Tingkatan'].append("Aliyah")
        data['Kelas'].append(cls)
        data['Rombel'].append(rombel)

df = pd.DataFrame(data)

output_dir = 'c:\\xampp\\htdocs\\erapor\\dummy_data_gen'
os.makedirs(output_dir, exist_ok=True)
output_path = os.path.join(output_dir, 'Data_Kelas_Dummy.xlsx')

df.to_excel(output_path, index=False)
print(f"Generated {output_path}")
