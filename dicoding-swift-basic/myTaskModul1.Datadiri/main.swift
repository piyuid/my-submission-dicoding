import Foundation
//Data Awal dalam mengisi variable data diri
print("Tugas Latihan untuk Modul 1")

print("Masukan Nama Depan Anda: "); let firstName = readLine()!
print("Masukan Nama Belakang Anda: "); lastName = readLine()!
print("Masukan Nama Alamat Anda: "); let address = readLine()!
print("Masukan Nama Pekerjaan Anda: "); let job = readLine()!
print("Masukan Nama Umur Anda: "); let age = readLine()!

let fullName = firstName + " " + lastName

print("----------------------------------------------")
//Area untuk output dari program yang akan di compile
print("Apakah kalian tahu \(fullName)?")
print("\(firstName) adalah seorang \(job)")
print("Saat ini dia berumur \(age) dan tempat tinggal didaerah \(address)")
print("----------------------------------------------")


